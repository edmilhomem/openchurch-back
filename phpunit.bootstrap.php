<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
$dboptions = array(
    "driver"    => "pdo_mysql",
    "host"      => "localhost",
    "dbname"    => "openchurch",
    "user"      => "root",
    "password"  => "root",
    "charset"   => "utf8mb4"
);
$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/src/OpenChurch/ORM/Mapping/"), true);
$em = EntityManager::create($dboptions, $config);
$em->getEventManager()->addEventSubscriber(new OpenChurch\Data\DataEventSubscriber());