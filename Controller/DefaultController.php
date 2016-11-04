<?php

namespace Mallapp\SimpleauthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Mallapp\SimpleauthBundle\Entity\BaseUser;

class DefaultController extends Controller
{
 
    
    public function createAction(Request $request)
    {
    
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('MallappSimpleauthBundle:BaseUser');

        
        $params = $this->getRequestBodyJsonParameters($request);
        
        if ($params == null) {
            return new JsonResponse(array('status' => 'nok', 'message' => 'INVALID_JSON'));
        }
        
        if (!array_key_exists('nickname', $params)) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'NO_NICKNAME'));
        
        }
        
        $nickname = $params['nickname'];
        
        $newUser = new BaseUser();
        
        if (array_key_exists('email', $params)) {
         
            // Check if already exists
            
            $email = $params['email'];
                    
            $existingUser = $repository->findOneByEmail($email);
            
            if ($existingUser != null) {
                
                return new JsonResponse(array('status' => 'nok', 'message' => 'EMAIL_USED'));
                
            }
            else {
                
                // Create new with email
                $newUser->setEmail($email);
                
            }
            
        }
        
        $newUser->setNickname($nickname);
        
        do {
        
            $tokenCandidate = BaseUser::createToken();
        
        } while ($repository->findOneByToken($tokenCandidate) != null);
            
        $newUser->setToken($tokenCandidate);
        
        $em->persist($newUser);
        
        $em->flush();
        
        return new JsonResponse(array('status' => 'ok', 'token' => $newUser->getToken()));
        
    }
    
    
    public function updatemailAction(Request $request)
    {
    
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('MallappSimpleauthBundle:BaseUser');

        $params = $this->getRequestBodyJsonParameters($request);
        
        if ($params == null) {
            return new JsonResponse(array('status' => 'nok', 'message' => 'INVALID_JSON'));
        }
        
        
        if (!array_key_exists('currentmail', $params)) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'NO_CURRENTMAIL'));
        
        }
        
        if (!array_key_exists('newmail', $params)) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'NO_NEWMAIL'));
        
        }
        
        if (!array_key_exists('token', $params)) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'INVALID_TOKEN'));
        
        }
        
        $currentmail = $params['currentmail'];
        $newmail = $params['newmail'];
        $token = $params['token'];

            
        $existingUser = $repository->findOneByEmail($currentmail);

        if ($existingUser == null) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'NO_USER'));
            
        }
        
        $existingUserNewMail = $repository->findOneByEmail($newmail);

        if ($existingUserNewMail != null) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'EMAIL_USED'));
            
        }
        
        // Check token
        
        if ($existingUser->getToken() != $token) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'INVALID_TOKEN'));
            
        }
           
        // Update existing
            
        $existingUser->setEmail($newmail);

        $em->flush();

        return new JsonResponse(array('status' => 'ok', 'token' => $existingUser->getToken()));

    }

    
    
    public function resendAction(Request $request)
    {

        $repository = $this->getDoctrine()->getRepository('MallappSimpleauthBundle:BaseUser');
        
        $params = $this->getRequestBodyJsonParameters($request);
        
        if ($params == null) {
            return new JsonResponse(array('status' => 'nok', 'message' => 'INVALID_JSON'));
        }
        
        
        if (!array_key_exists('email', $params)) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'NO_EMAIL'));
        
        }
        
        $existingUser = $repository->findOneByEmail($params['email']);

        if ($existingUser == null) {
            
            return new JsonResponse(array('status' => 'nok', 'message' => 'NO_USER'));
            
        }
        
        $message = \Swift_Message::newInstance()
            ->setSubject('Your Password')
            ->setFrom('noreply@mallapp.ch')
            ->setTo($existingUser->getEmail())
            ->setBody(
                $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                    'MallappSimpleauthBundle:Emails:resend.html.twig',
                    array('name' => $existingUser->getNickname(), 'code' => $existingUser->getToken())
                ),
                'text/html'
            );
        
        $this->get('mailer')->send($message);

        return new JsonResponse(array('status' => 'ok'));
    
    }
    
    private function getRequestBodyJsonParameters(Request $request) {
        
        $params = array();
        $content = $request->getContent();
        if (!empty($content))
        {
            $params = json_decode($content, true);
        }
        
        return $params;
        
    }
}
