<?php

/**
 * @file
 * Contains \DrupalCI\Tests\Plugin\BuildSteps\generic\CommandTest.
 */

namespace DrupalCI\Tests\Plugin\BuildSteps\generic;

use Docker\Container;
use DrupalCI\Plugin\BuildSteps\generic\ContainerCommand;
use DrupalCI\Tests\DrupalCITestCase;

/**
 * @covers ContainerCommand
 */
class ContainerCommandTest extends DrupalCITestCase {

  function testRun() {
    $cmd = 'test_command test_argument';
    $instance = new Container([]);

    $body = $this->getMock('GuzzleHttp\Stream\StreamInterface');

    $response = $this->getMock('GuzzleHttp\Message\ResponseInterface');
    $response->expects($this->once())
      ->method('getBody')
      ->will($this->returnValue($body));

    $container_manager = $this->getMockBuilder('Docker\Manager\ContainerManager')
      ->disableOriginalConstructor()
      ->getMock();
    $container_manager->expects($this->once())
      ->method('find')
      ->will($this->returnValue($instance));
    $container_manager->expects($this->once())
      ->method('exec')
      ->with($instance, ['/bin/bash', '-c', $cmd], TRUE, TRUE, TRUE, TRUE)
      ->will($this->returnValue(1));
    $container_manager->expects($this->once())
      ->method('execstart')
      ->will($this->returnValue($response));

    $docker = $this->getMockBuilder('Docker\Docker')
      ->disableOriginalConstructor()
      ->getMock();
    $docker->expects($this->once())
      ->method('getContainerManager')
      ->will($this->returnValue($container_manager));

    $this->job->expects($this->once())
      ->method('getDocker')
      ->will($this->returnValue($docker));
    $this->job->expects($this->once())
      ->method('getExecContainers')
      ->will($this->returnValue(['php' => [['id' => 'dockerci/php-5.4']]]));

    $command = new ContainerCommand();
    $command->run($this->job, $cmd);
  }

}
