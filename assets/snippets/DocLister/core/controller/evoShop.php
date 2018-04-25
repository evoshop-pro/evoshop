<?php
include_once(dirname(__FILE__) . "/site_content.php");
require_once MODX_BASE_PATH . 'assets/modules/evoShop/evoshop.class.php';


class evoShopDocLister extends site_contentDocLister
{

    public function __construct($modx, $cfg){
        $this->es = evoShop::getInstance($modx);
        $this->es->init();

        $cfg['tvPrefix'] = '';
        $tvList = explode(',',$cfg['tvList']);

        $cfg['tvList'] = implode(',',array_merge($tvList, [
            $this->es->config['generalTV']['unit'],
            $this->es->config['generalTV']['price'],
            $this->es->config['generalTV']['step'],
            $this->es->config['generalTV']['currency'],
            $this->es->config['generalTV']['title']
        ]));

        parent::__construct($modx, $cfg);

       /* $this->prod_arr = [];

        foreach($this->es->cart->cart['items'] as $hash=>$item) {
            $this->prod_arr[] = $item['id'];
        }*/
    }

    public function _render($tpl = '')
    {   
        $out = '';
        $separator = $this->getCFGDef('outputSeparator', '');
        if ($tpl == '') {
            $tpl = $this->getCFGDef('tpl', '@CODE:<a href="[+url+]">[+pagetitle+]</a><br />');
        }
        if ($tpl != '') {
            $this->toPlaceholders(count($this->_docs), 1, "display"); // [+display+] - сколько показано на странице.

            $i = 1;
            $sysPlh = $this->renameKeyArr($this->_plh, $this->getCFGDef("sysKey", "dl"));
            if (count($this->_docs) > 0) {
                /**
                 * @var $extUser user_DL_Extender
                 */
                if ($extUser = $this->getExtender('user')) {
                    $extUser->init($this, array('fields' => $this->getCFGDef("userFields", "")));
                }

                /**
                 * @var $extSummary summary_DL_Extender
                 */
                $extSummary = $this->getExtender('summary');

                /**
                 * @var $extPrepare prepare_DL_Extender
                 */
                $extPrepare = $this->getExtender('prepare');

                $this->skippedDocs = 0;
                foreach ($this->_docs as $item) {
                    $this->renderTPL = $tpl;
                    if ($extUser) {
                        $item = $extUser->setUserData($item); //[+user.id.createdby+], [+user.fullname.publishedby+], [+dl.user.publishedby+]....
                    }

                    $item['summary'] = $extSummary ? $this->getSummary($item, $extSummary, 'introtext', 'content') : '';

                    $item = array_merge($item,
                        $sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
                    $item['iteration'] = $i; //[+iteration+] - Number element. Starting from zero

                    $item['title'] = ($item['menutitle'] == '' ? $item['pagetitle'] : $item['menutitle']);

                    if ($this->getCFGDef('makeUrl', 1)) {
                        if ($item['type'] == 'reference') {
                            $item['url'] = is_numeric($item['content']) ? $this->modx->makeUrl($item['content'], '', '',
                                $this->getCFGDef('urlScheme', '')) : $item['content'];
                        } else {
                            $item['url'] = $this->modx->makeUrl($item['id'], '', '', $this->getCFGDef('urlScheme', ''));
                        }
                    }
                    $date = $this->getCFGDef('dateSource', 'pub_date');
                    if (isset($item[$date])) {
                        if (!$item[$date] && $date == 'pub_date' && isset($item['createdon'])) {
                            $date = 'createdon';
                        }
                        $_date = is_numeric($item[$date]) && $item[$date] == (int)$item[$date] ? $item[$date] : strtotime($item[$date]);
                        if ($_date !== false) {
                            $_date = $_date + $this->modx->config['server_offset_time'];
                            $dateFormat = $this->getCFGDef('dateFormat', '%d.%b.%y %H:%M');
                            if ($dateFormat) {
                                $item['date'] = strftime($dateFormat, $_date);
                            }
                        }
                    }

                    $findTpl = $this->renderTPL;
                    $tmp = $this->uniformPrepare($item, $i);
                    extract($tmp, EXTR_SKIP);
                    if ($this->renderTPL == '') {
                        $this->renderTPL = $findTpl;
                    }

                    if ($extPrepare) {
                        $item = $extPrepare->init($this, array(
                            'data'      => $item,
                            'nameParam' => 'prepare'
                        ));
                        if (is_bool($item) && $item === false) {
                            $this->skippedDocs++;
                            continue;
                        }
                    }

                    $item = $this->evoShopPrepare($item);

                    $tmp = $this->parseChunk($this->renderTPL, $item);

                    if ($this->getCFGDef('contentPlaceholder', 0) !== 0) {
                        $this->toPlaceholders($tmp, 1,
                            "item[" . $i . "]"); // [+item[x]+] – individual placeholder for each iteration documents on this page
                    }
                    $out .= $tmp;
                    if (next($this->_docs) !== false) {
                        $out .= $separator;
                    }
                    $i++;
                }
            } else {
                $noneTPL = $this->getCFGDef('noneTPL', '');
                $out = ($noneTPL != '') ? $this->parseChunk($noneTPL, $sysPlh) : '';
            }
            $out = $this->renderWrap($out);
        }

        return $this->toPlaceholders($out);
    }

    private function evoShopPrepare ($data) {
        
        //$itemAdded = in_array($data['id'], $this->prod_arr) ? $this->es->config['classes']['addedClass'] : '';
 
        //$data['btnTxt'] = $itemAdded ? $this->es->config['buttonText']['buttonTextAfterAdd'] : $this->es->config['buttonText']['buttonTextDefault'];


        //Устанавливаем классы
        $itemClass = $this->es->config['classes']['itemClass'] ? $this->es->config['classes']['itemClass'] : 'es-item';
        $data['es-classes'] = $itemClass.' '.$itemClass.'-'.$data['id'];


        //Форматируем цену
        $data['price'] = $this->es->price_format($this->es->toCurrency($data['price'], $data['currency']));
        $data['unit'] = $data[$this->es->config['generalTV']['unit']];
        $data['step'] = $data[$this->es->config['generalTV']['step']];
        $data['title'] = $data[$this->es->config['generalTV']['title']] ?: $data['pagetitle'];

        
        $data = array_merge($data, $this->es->blang);  
            
        return $data;
    }
}
