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
                
                // Update existing
                $existingUser->setNickname($nickname);
        
                $em->flush();
                
                return new JsonResponse(array('status' => 'ok', 'token' => $existingUser->getToken()));
                
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
    
    public function resendAction(Request $request)
    {
        return $this->render('MallappSimpleauthBundle:Default:index.html.twig');
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
