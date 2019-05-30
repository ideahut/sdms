<?php
namespace Ideahut\sdms\response;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Ideahut\sdms\util\ObjectUtil;

class MediaTypeResponse
{
	public function response(Request $request, Response $response, $object = null, $type = null) {
		$accept = $request->getHeaderLine("Accept");
		if ($accept !== "" && $accept !== "*/*") {
			if (strpos($accept, "application/json") !== false) {
				return (new JsonResponse())->response($request, $response, $object, "application/json");
			} else if (strpos($accept, "text/xml") !== false) {
				return (new XmlResponse())->response($request, $response, $object, "text/xml");
			} else if (strpos($accept, "application/xml") !== false) {
				return (new XmlResponse())->response($request, $response, $object, "application/xml");
			}
		}
		$type = $request->getMediaType();
		if (strpos($type, "text/xml") !== false || strpos($type, "application/xml") !== false) {
			return (new XmlResponse())->response($request, $response, $object, $type);
		} else {
			return (new JsonResponse())->response($request, $response, $object, "application/json");
		}
        return $response;
	}
}