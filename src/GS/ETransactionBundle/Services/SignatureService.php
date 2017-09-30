<?php

namespace GS\ETransactionBundle\Services;

use GS\ETransactionBundle\Event\IpnEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SignatureService
{
    /**
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(RequestStack $requestStack,
            EventDispatcherInterface $dispatcher, $publicKey)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->publicKey = $publicKey;
    }

    protected function initData()
    {
        foreach ($this->getRequestParameters() as $key => $value) {
            if ('sign' !== $key) {
                $this->data[$key] = urlencode($value);
            }
        }
    }

    /**
     * Makes an array of parameters become a querystring like string.
     *
     * @param  array $array
     *
     * @return string
     */
    protected function stringify(array $array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = sprintf('%s=%s', $key, $value);
        }
        return implode('&', $result);
    }

    protected function getRequestParameters()
    {
        if ($this->request->isMethod('POST')) {
            $parameters = $this->request->request;
        } else {
            $parameters = $this->request->query;
        }
        return $parameters;
    }

    protected function initSignature()
    {
        if (!$this->getRequestParameters()->has('sign')) {
            return false;
        }
        $signature = $this->getRequestParameters()->get('sign');
        $signatureLength = strlen($signature);
        if ($signatureLength > 172) {
            $this->signature = base64_decode(urldecode($signature));
            return true;
        } elseif ($signatureLength == 172) {
            $this->signature = base64_decode($signature);
            return true;
        } elseif ($signatureLength == 128) {
            $this->signature = $signature;
            return true;
        } else {
            $this->signature = null;
            return false;
        }
    }

    public function verifySignature()
    {
        $this->initData();
        $this->initSignature();

        $file = fopen($this->publicKey, 'r');
        $cert = fread($file, 1024);
        fclose($file);

        $publicKey = openssl_pkey_get_public($cert);

        $result = openssl_verify(
            $this->stringify($this->data),
            $this->signature,
            $publicKey,
            'sha1WithRSAEncryption'
        );
        $result = (1 == $result);

        openssl_free_key($publicKey);

        $event = new IpnEvent($this->data, $result);
        $this->dispatcher->dispatch(IpnEvent::NAME, $event);

        return $result;
    }
}