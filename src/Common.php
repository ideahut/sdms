<?php
namespace Ideahut\sdms;

final class Common
{

	const SELF_DIR = __DIR__;
	
	/**
	 * PARAMETER
	 */
	const PARAMETER_PAGE_INDEX		= "p_index";
	const PARAMETER_PAGE_SIZE		= "p_size";
	const PARAMETER_ORDER			= "p_order";
	const PARAMETER_GROUP			= "p_group";
	const PARAMETER_LIMIT			= "p_limit";
	
	
	/**
	 * SPLIT
	 */
	const SPLIT_ORDER_FIELD			= ",";
	const SPLIT_ORDER_SPEC			= "-";
	const SPLIT_GROUP_FIELD			= ",";
	const SPLIT_GROUP_SPEC			= "-";
	const SPLIT_OBJECT_FIELD		= "-";
	
		
	/**
	 * HEADER
	 */
	const HEADER_USER_AGENT			= "User-Agent";
	const HEADER_USER_AGENT_SLIM	= "HTTP_USER_AGENT";
	
	
	/**
	 * PAGE
	 */
	const PAGE_DEFAULT_SIZE			= 20;


	/**
	 * SETTING
	 */
	const SETTING_SETTINGS			= "settings";
	const SETTING_APP_DIR			= "app-dir";
	const SETTING_PUBLIC_DIR		= "public-dir";
	const SETTING_NAMESPACE_DIR		= "namespace-dir";
	const SETTING_ROUTE				= "route";
	const SETTING_CONTROLLER		= "controller";
	const SETTING_METHOD			= "method";
	const SETTING_PATH				= "path";
	const SETTING_RESPONSE			= "response";	
	const SETTING_ACCESS			= "access";
	const SETTING_CLASS				= "class";
	const SETTING_PARAMETER			= "parameter";
	const SETTING_DOCUMENT			= "document";
	const SETTING_SUFFIX			= "suffix";
	const SETTING_ENTITY			= "entity";
	const SETTING_LOGGER			= "logger";
	const SETTING_CACHE				= "cache";
	const SETTING_ENTITY_MANAGER	= "entityManager";	// Doctrine
	const SETTING_DATABASE			= "database";		// PDO
	const SETTING_SEQUENCE			= "sequence";
	const SETTING_MAX				= "max";
	const SETTING_START				= "start";
	const SETTING_ADMIN				= "admin";
	const SETTING_DEBUG				= "debug";
	const SETTING_HESSIAN			= "hessian";
	const SETTING_SERVICE			= "service";
	const SETTING_IS_PUBLIC			= "is_public";
	const SETTING_FORMAT_SHOW_NULL	= "format_show_null";

	const SETTING_CORS_ORIGIN		= "cors-origin";
	const SETTING_CORS_METHODS		= "cors-methods";
	const SETTING_CORS_HEADERS		= "cors-headers";
	

	
	/**
	 * RESPONSE TYPE
	 */	
	const TYPE_JSON			= 1;
	const TYPE_XML			= 2;
	const TYPE_TEXT			= 3;
	
	
	/**
	 * ACCESS
	 */
	const ACCESS_EXPIRED = 86400; // detik
	



	/*
	 * INIT
	 */
	private static $SETTINGS = [];

	public static function init($value = []) {
		\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
		self::$SETTINGS = isset($value['settings']) ? $value['settings'] : [];		
		if (isset($value['ignore_annotation'])) {
			$ignore = $value['ignore_annotation'];
			if (is_array($ignore)) {
				foreach ($ignore as $str) {
					\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($str);
				}
			} 
			else if (is_string($ignore)){
				\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($ignore);
			}
		}
	}

	public static function getSettings() {
		return self::$SETTINGS;
	}

}