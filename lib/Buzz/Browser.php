<?php

namespace Buzz;

use Buzz\Client;
use Buzz\History;
use Buzz\Message;

class Browser
{
  protected $client;
  protected $journal;
  protected $requestFactory;
  protected $responseFactory;

  public function __construct(Client\ClientInterface $client = null, History\Journal $journal = null)
  {
    $this->setClient($client ?: new Client\FileGetContents());
    $this->setJournal($journal ?: new History\Journal());
  }

  public function get($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_GET, $headers);
  }

  public function post($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_POST, $headers);
  }

  public function head($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_HEAD, $headers);
  }

  public function put($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_PUT, $headers);
  }

  public function delete($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_DELETE, $headers);
  }

  /**
   * Sends a request and adds the call to the journal.
   * 
   * @param string $url     The URL to call
   * @param string $method  The request method to use
   * @param array  $headers An array of request headers
   * 
   * @return Message\Response The response object
   */
  public function call($url, $method, $headers = array())
  {
    $request = $this->getNewRequest();

    $request->setMethod($method);
    $request->fromUrl($url);
    $request->addHeaders($headers);

    return $this->send($request);
  }

  /**
   * Sends a request and records it to the journal.
   * 
   * @param Message\Request  $request  A request object
   * @param Message\Response $response A response object
   * 
   * @return Message\Response A response object
   */
  public function send(Message\Request $request, Message\Response $response = null)
  {
    if (null === $response)
    {
      $response = $this->getNewResponse();
    }

    $this->getClient()->send($request, $response);
    $this->getJournal()->record($request, $response);

    return $response;
  }

  /**
   * Returns a DOMDocument for the current response.
   * 
   * @return DOMDocument
   */
  public function getDom()
  {
    return $this->getJournal()->getLastResponse()->toDomDocument();
  }

  public function setClient(Client\ClientInterface $client)
  {
    $this->client = $client;
  }

  public function getClient()
  {
    return $this->client;
  }

  public function setJournal(History\Journal $journal)
  {
    $this->journal = $journal;
  }

  public function getJournal()
  {
    return $this->journal;
  }

  public function setRequestFactory($callable)
  {
    $this->requestFactory = $callable;
  }

  public function getRequestFactory()
  {
    return $this->requestFactory;
  }

  public function setResponseFactory($callable)
  {
    $this->responseFactory = $callable;
  }

  public function getResponseFactory()
  {
    return $this->responseFactory;
  }

  public function getNewRequest()
  {
    if ($callable = $this->getRequestFactory())
    {
      return $callable();
    }

    return new Message\Request();
  }

  public function getNewResponse()
  {
    if ($callable = $this->getResponseFactory())
    {
      return $callable();
    }

    return new Message\Response();
  }
}
