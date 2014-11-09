<?php
namespace Product\Util;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class ControllerUtil
{
	public static function generateErrorViewModel($response, $variables)
	{
		$viewModel = new ViewModel();
		$viewModel->setTemplate('product/error');
		$viewModel->setVariables(array('result' => $variables));
		$viewModel->setTerminal(true);
	
		$response->setStatusCode(401);
		 
		return $viewModel;
	}
	
	public static function generateAjaxViewModel($response, $variables)
	{
		$jsonModel = new JsonModel($variables);

		if ($variables['result'] !== true)
			$response->setStatusCode(401);
		
		return $jsonModel;
	}
	
	/**
	 * @param course_id
	 * @param auth
	 * @param is_authenticated
	 */
	public static function checkAuthentication(ServiceLocatorInterface $serviceLocator, $course_id = null)
	{
		$auth = $serviceLocator->get('auth');
		if ($auth->hasIdentity())
		{
			$auth_status = array('is_authenticated' => true);
				
			$storage = $auth->getStorage()->read();
				
			$auth_status['user'] = $storage['user'];
				
			return $auth_status;
		}
		else
			return array('is_authenticated' => false);
	}
        
        /**************************************************************
        *
        *	使用特定function对数组中所有元素做处理
        *	@param	string	&$array		要处理的字符串
        *	@param	string	$function	要执行的函数
        *	@return boolean	$apply_to_keys_also		是否也应用到key上
        *	@access public
        *
        *************************************************************/
       function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
       {
           static $recursive_counter = 0;
           if (++$recursive_counter > 1000) {
               die('possible deep recursion attack');
           }
           foreach ($array as $key => $value) {
               if (is_array($value)) {
                   arrayRecursive($array[$key], $function, $apply_to_keys_also);
               } else {
                   $array[$key] = $function($value);
               }

               if ($apply_to_keys_also && is_string($key)) {
                   $new_key = $function($key);
                   if ($new_key != $key) {
                       $array[$new_key] = $array[$key];
                       unset($array[$key]);
                   }
               }
           }
           $recursive_counter--;
       }

       /**************************************************************
        *
        *	将数组转换为JSON字符串（兼容中文）
        *	@param	array	$array		要转换的数组
        *	@return string		转换得到的json字符串
        *	@access public
        *
        *************************************************************/
       function JSON($array) {
               arrayRecursive($array, 'urlencode', true);
               $json = json_encode($array);
               return urldecode($json);
       }
       
        public static function checkCaptcha($strAns) {
            session_start();
            if ($_SESSION['string'] == $strAns) {
                return true;
            }
            return false;
        }

}