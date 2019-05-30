<?php
namespace Ideahut\sdms\response;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Ideahut\sdms\util\ObjectUtil;

class XmlResponse
{
	
	public function response(Request $request, Response $response, $object = null, $type = null) {
		$data = ObjectUtil::formatObject($object);
		if (isset($data)) {
			$xml = $this->arrayToXML($data);
			$out = $response->getBody();
			$xml = $xml->asXML();
			$out->write($xml);
		}
        return $response->withHeader("Content-Type", isset($type) ? $type : "text/xml");
	}

	private function arrayToXML(array $array) {
		$result = new \SimpleXMLElement("<object/>");
		if (array_keys($array) !== range(0, count($array) - 1)) {
			foreach ($array as $key => $value) {
				$this->setNodeValue($result, $key, $value);
			}
		} else {
			$this->setNodeValue($result, "collection", $array);
		}
		return $result;
	}

	private function setNodeValue(&$node, $key, $value) {
		if (is_array($value)) {
			if (array_keys($value) !== range(0, count($value) - 1)) {
				$subnode = $node->addChild($key);
				foreach ($value as $k => $v) {
					$this->setNodeValue($subnode, $k, $v);
				}
			} else {
				foreach ($value as $v) {
					if (is_array($v)) {
						$this->setNodeValue($node, $key, $v);
					} else {
						$node->addChild($key, htmlspecialchars($v));			
					}
				}
			}
		} else {
			$node->addChild($key, htmlspecialchars($value));
		}
	}

}