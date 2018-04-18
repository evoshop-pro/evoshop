/**
 * evoShopAjax
 *
 * @category    plugin
 * @version     0.1
 * @internal    @properties
 * @internal    @events OnPageNotFound
 * @internal    @modx_category evoShop
 * @internal    @installset base
 * @reportissues https://github.com/modxcms/evolution
 * @author      Bilbo Baggins aka autogen
 * @lastupdate  04/18/2018
 */

if(!defined('MODX_BASE_PATH')) die('What are you doing? Get out of here!');

$e = &$modx->Event;

switch($e->name){       
        
    case 'OnPageNotFound':      

        switch($_GET['q']){     
            case 'evoshop-ajax':
                require_once MODX_BASE_PATH . 'assets/modules/evoShop/ajax.php';
                die();      
            break;
                
            case 'evoshop-test':
                $docLister = $modx->runSnippet('DocLister', ['parents'=>1, 'display'=>3]);
                echo 'Новый текст блока popup.<br/>'.$docLister.'<br/><a href="#" class="closeBtn">Закрыть</a>';
                die();
            break;
                
        }
        break;
}       