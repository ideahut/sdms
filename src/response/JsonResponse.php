<?php
namespace Ideahut\sdms\response;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Ideahut\sdms\util\ObjectUtil;

class JsonResponse
{
	
	public function response(Request $request, Response $response, $object = null, $type = null) {
		$data = ObjectUtil::formatObject($object);
		$response = $response->withJson($data);
        return $response->withHeader("Content-Type", isset($type) ? $type : "application/json");
	}

}