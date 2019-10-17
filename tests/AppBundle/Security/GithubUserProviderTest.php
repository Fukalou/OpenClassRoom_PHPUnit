<?php

namespace Tests\AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Security\GithubUserProvider;
use PHPUnit\Framework\TestCase;

class GithubUserProviderTest extends TestCase
{
    private $client, $serializer, $streamedResponce, $responce;
    public function setUp()
    {
        $this->client = $this
            ->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->serializer = $this
            ->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responce = $this
            ->getMockBuilder('Psr\Http\Message\MessageInterface')
            ->getMock();

        $this->streamedResponce = $this
            ->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();
    }
    public function testLoadUserByUsernameReturningAUser()
    {

        $this->client->expects($this->once())
            ->method('get')
            ->willReturn($this->responce);

        $this->responce->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamedResponce);

        $userData = ['login' => 'a login',
            'name' => 'user name',
            'email' => 'adress@mail.com',
            'avatar_url' => 'url to the avatar',
            'html_url' => 'url to profile'];
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $user = $githubUserProvider->loadUserByUsername('an-acesse-token');

        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);
        
        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('AppBundle\Entity\User', get_class($user));
    }

    public function testLoadUserByUsernameThrowingException()
    {
        $this->client->expects($this->once())
            ->method('get')
            ->willReturn($this->responce);

        $this->responce->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamedResponce);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->willReturn([]);

        $this->expectException('LogicException');

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $githubUserProvider->loadUserByUsername('an-acesse-token');
    }

    public function tearDown()
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }
}