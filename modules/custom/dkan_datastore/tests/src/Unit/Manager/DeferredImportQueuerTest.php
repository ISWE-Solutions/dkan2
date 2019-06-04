<?php

namespace Drupal\Tests\dkan_datastore\Unit\Manager;

use Drupal\dkan_datastore\Manager\DeferredImportQueuer;
use Drupal\dkan_common\Tests\DkanTestBase;
use Dkan\Datastore\Resource;
use Drupal\Core\Queue\QueueInterface;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Manager\DeferredImportQueuer
 * @group dkan_datastore
 */
class DeferredImportQueuerTest extends DkanTestBase {

  /**
   * Tests CreateDeferredResourceImport().
   */
  public function testCreateDeferredResourceImport() {
    // setup
    $mock = $this->getMockBuilder(DeferredImportQueuer::class)
      ->setMethods([
        'getQueue'
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockQueue = $this->getMockBuilder(QueueInterface::class)
      ->setMethods(['createItem'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockResource = $this->getMockBuilder(Resource::class)
      ->setMethods([
        'getId',
        'getFilePath',
      ])
      ->disableOriginalConstructor()
      ->getMock();


    $uuid             = uniqid('uuid');
    $resourceId       = uniqid('42');
    $resourceFilePath = uniqid('scheme://foo/bar');
    $importConfig     = ['foo'];
    $expected         = 42;

    // expect
    $mock->expects($this->once())
      ->method('getQueue')
      ->willReturn($mockQueue);

    $mockResource->expects($this->once())
      ->method('getId')
      ->willReturn($resourceId);

    $mockResource->expects($this->once())
      ->method('getFilePath')
      ->willReturn($resourceFilePath);

    $mockQueue->expects($this->once())
      ->method('createItem')
      ->with([
        'uuid'          => $uuid,
        'resource_id'   => $resourceId,
        'file_path'     => $resourceFilePath,
        'import_config' => $importConfig,
      ])
      ->willReturn($expected);

    // assert
    $actual = $mock->createDeferredResourceImport(
      $uuid,
      $mockResource,
      $importConfig
    );
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests CreateDeferredResourceImport() for exception condition.
   */
  public function testCreateDeferredResourceImportOnException() {
    // setup
    $mock = $this->getMockBuilder(DeferredImportQueuer::class)
      ->setMethods([
        'getQueue'
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockQueue = $this->getMockBuilder(QueueInterface::class)
      ->setMethods(['createItem'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockResource = $this->getMockBuilder(Resource::class)
      ->setMethods([
        'getId',
        'getFilePath',
      ])
      ->disableOriginalConstructor()
      ->getMock();


    $uuid             = uniqid('uuid');
    $resourceId       = uniqid('42');
    $resourceFilePath = uniqid('scheme://foo/bar');
    $importConfig     = ['foo'];
    $queueId         = FALSE;

    // expect
    $mock->expects($this->once())
      ->method('getQueue')
      ->willReturn($mockQueue);

    $mockResource->expects($this->once())
      ->method('getId')
      ->willReturn($resourceId);

    $mockResource->expects($this->once())
      ->method('getFilePath')
      ->willReturn($resourceFilePath);

    $mockQueue->expects($this->once())
      ->method('createItem')
      ->with([
        'uuid'          => $uuid,
        'resource_id'   => $resourceId,
        'file_path'     => $resourceFilePath,
        'import_config' => $importConfig,
      ])
      ->willReturn($queueId);

    $this->setExpectedException(\RuntimeException::class, "Failed to create file fetcher queue for {$uuid}");

    // assert
    $actual = $mock->createDeferredResourceImport(
      $uuid,
      $mockResource,
      $importConfig
    );
    $this->assertEquals($queueId, $actual);
  }



}
