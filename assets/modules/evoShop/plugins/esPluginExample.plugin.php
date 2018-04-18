/**
 * esPluginExample
 *
 * @category    plugin
 * @version     0.1
 * @internal    @properties
 * @internal    @events esBeforeAddToCart,esOnRenderCart,esOnAddToCart,esOnChangeInCart,esOnBeforeRemoveFromCart,esOnRemoveFromCart,esOnLoadCart
 * @internal    @modx_category evoShop
 * @internal    @installset base
 * @reportissues https://github.com/modxcms/evolution
 * @author      Bilbo Baggins aka autogen
 * @lastupdate  04/18/2018
 */

if(!defined('MODX_BASE_PATH')) die('What are you doing? Get out of here!');
$e = &$modx->Event;

switch($e->name){       
        
    case 'esBeforeAddToCart':
        //$modx->logEvent(123, 3, '<pre>'.print_r($data, true).'</pre>', 'evoShop - esPluginExample - '.$e->name);
    break;
        
    case 'esOnRenderCart':
        //$modx->logEvent(123, 3, '<pre>'.print_r($data, true).'</pre>', 'evoShop - esPluginExample - '.$e->name);
        
        foreach($data['cart']['items'] as $hash=>$item) {
            //Добавляем к товару уменьшенную картинку
            $data['cart']['items'][$hash]['thumb']= $modx->runSnippet('phpthumb', ['input'=>$item['imgFull'], 'options'=>'w=55,h=55,zc=1']);

            //Форматируем вывод выбранных параметров в строке корзины
            if(is_array($item['options'])) {
                $options='';
                foreach($item['options'] as $k=>$param){
                    if(is_array($param)) {
                        $param = implode(',',$param);
                    }
                    $options .= '<tr><td>'.$es->translate('[%'.$k.'%]').':</td><td>'.$es->translate($param).'</td></tr>';
                }
            }   
            $data['cart']['items'][$hash]['params'] = $options;
        }


            //Возвращаем сериализованные данные
            $e->output(serialize($data));
        break;
        
        case 'esOnAddToCart':
        case 'esOnChangeInCart':
        //$modx->logEvent(123, 3, '<pre>'.print_r($data, true).'</pre>', 'evoShop - esPluginExample - '.$e->name);
            $hash = $data['hash'];
            $cart = $data['cart'];

        
            if($cart[$hash]['quantity']>1) {
                $data['cart'][$hash]['price'] = $data['cart'][$hash]['price'] - ($data['cart'][$hash]['price'] * 10/100);
            }
        
            if($cart[$hash]['quantity']>4) {
                $data['cart'][$hash]['price'] = $data['cart'][$hash]['price'] - ($data['cart'][$hash]['price'] * 20/100);
            }
            
            if($cart[$hash]['quantity']>9) {
                $data['cart'][$hash]['price'] = $data['cart'][$hash]['price'] - ($data['cart'][$hash]['price'] * 30/100);
            }
        
            $e->output(serialize($data));
        break;
        
        case 'esOnBeforeRemoveFromCart':
            //$modx->logEvent(123, 3, '<pre>'.print_r($data, true).'</pre>', 'evoShop - esPluginExample - '.$e->name);
        break;
        
        case 'esOnRemoveFromCart':
            //$modx->logEvent(123, 3, '<pre>'.print_r($data, true).'</pre>', 'evoShop - esPluginExample - '.$e->name);
        break;
        
        case 'esOnLoadCart':
            //$modx->logEvent(123, 3, '<pre>'.print_r($data, true).'</pre>', 'evoShop - esPluginExample - '.$e->name);
        break;
        
}