<?php

require_once(dirname(__DIR__) . '/IyzipayBootstrap.php');

IyzipayBootstrap::init();

class Config
{
    public static function options()
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey('sandbox-1Zac7jEmE4RdSegcDh0uh3NjQ4jaaJNk');
        $options->setSecretKey('sandbox-3YQ53HSY8SmyIzjQN5FQ6a6q4YXUCfDN');
        $options->setBaseUrl('https://sandbox-api.iyzipay.com');

        return $options;
    }
}
