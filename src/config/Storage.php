<?php
use Google\Cloud\Storage\StorageClient;

class Storage {

    // private $CRED_FILE = "../src/config/findhandyman-f0b74-4d16b7fd9ffb.json";
    // private $BUCKET_NAME = "findhandyman-f0b74.appspot.com";
    private $bucket;

    public function __construct() {
		$storage = new StorageClient([
            'keyFilePath' => "../src/config/findhandyman-f0b74-4d16b7fd9ffb.json"
        ]);

        $this->bucket = $storage->bucket("findhandyman-f0b74.appspot.com");
    }

    public function getUrl($file) {
        $object = $this->bucket->object($file);
        $d = new DateTime('tomorrow');
        return $object->signedUrl($d);
    }
}

?>