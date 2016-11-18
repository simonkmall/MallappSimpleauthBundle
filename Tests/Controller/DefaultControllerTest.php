<?php

namespace Mallapp\SimpleauthBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class DefaultControllerTest extends WebTestCase
{
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        // Make sure we are in the test environment
        if ('test' !== static::$kernel->getEnvironment()) {
            throw new \LogicException('Test must be executed in the test environment');
        }
        
        // Run the schema update tool using our entity metadata
        $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->updateSchema($metadatas);

                
    }


    public function testUserCreationFunctions()
    {
        $client = static::createClient();

        $client->request('POST', 
                '/simpleauth/create',
                array(),
                array(),
                array(),
                json_encode(array('nickname' => 'habasch', 'email' => 'habasch@mail.com'))
                );

        $responseData = $this->getAndCheckResponseJson($client);
        
        // Check for all keys
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('token', $responseData);
        
        // Check for OK status
        $this->assertEquals('ok', $responseData['status']);
        
        // Store TOKEN
        $currentToken = $responseData['token'];
        
        // Check storage into DB
        $baseUser = $this->em->getRepository('MallappSimpleauthBundle:BaseUser')->findOneByToken($currentToken);
        
        $this->assertNotNull($baseUser);
        $this->assertEquals('habasch@mail.com',$baseUser->getEmail());
        $this->assertEquals('habasch',$baseUser->getNickname());
        
        // Clear the doctrine cache
        $this->em->clear();
        
        $client->request('POST', 
                '/simpleauth/updatemail',
                array(),
                array(),
                array(),
                json_encode(array('token' => $currentToken, 'currentmail' => 'habasch@mail.com', 'newmail' => 'johann@mail.com'))
                );

        $responseDataTwo = $this->getAndCheckResponseJson($client);  
        
        // Check for all keys
        $this->assertArrayHasKey('status', $responseDataTwo);
        $this->assertArrayHasKey('token', $responseDataTwo);
        
        // Check for OK status
        $this->assertEquals('ok', $responseDataTwo['status']);
        
         // Check storage into DB
        $baseUserTwo = $this->em->getRepository('MallappSimpleauthBundle:BaseUser')->findOneByToken($currentToken);
        
        $this->assertNotNull($baseUserTwo);
        $this->assertEquals('johann@mail.com',$baseUserTwo->getEmail());
        
        // Check resend
        
        $client->request('POST', 
                '/simpleauth/resend',
                array(),
                array(),
                array(),
                json_encode(array('email' => 'johann@mail.com'))
                );

        $responseDataThree = $this->getAndCheckResponseJson($client);  
        
        // Check for all keys
        $this->assertArrayHasKey('status', $responseDataThree);
        
        // Check for OK status
        $this->assertEquals('ok', $responseDataThree['status']);       
        
        
    }
    
    
    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->createQuery('DELETE FROM MallappSimpleauthBundle:BaseUser')->execute();
        $this->em->close();
        $this->em = null; // avoid memory leaks
    }
    
    
    private function getAndCheckResponseJson($client) {
        
        $response = $client->getResponse();
        
        // Check for OK response
        $this->assertSame(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        
        // Check for valid json response
        $this->assertNotNull($responseData);
        
        return $responseData;
        
    }
    
}
