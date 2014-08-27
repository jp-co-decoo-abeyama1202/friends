<?php
/**
 * 絵文字使うためのクラス
 */
require_once(__DIR__ . '/HTML/Emoji.php');
namespace library;
class Emoji extends HTML_Emoji{
    /**
     * @var Config 
     */
    protected $_config;
    protected $_instances;
    
    function __construct(Config $config) 
    {
        $this->_config = $config;
        $conf = $config->getEmojiConfig();
        if($conf) {
            $instances = isset($conf['instances']) ? $conf['instances'] : array();
            if(!is_array($instances)) {
                $instances = array($instances);
            }
            foreach($instances as $instance) {
                $this->_instances[$instance] = parent::getInstance($instance);
            }
            if(isset($conf['image_url']) && file_exists($conf['image_url'])) {
                $this->setImageUrl($conf['image_url']);
            }
        }
    }
    
    /**
     * Instanceを新規に作って確保する。
     * 対応していない名前入れるとPCになる。
     * @param type $instance
     */
    public function addInstance($instance)
    {
        $this->_instances[$instance] = parent::getInstance($instance);
    }
    
    /**
     * 絵文字を他キャリアで表示できるように
     * @param type $text
     * @param type $key
     */
    public function convertCarrier($text,$key=null)
    {
        $i_cnt = count($this->_instances);
        if(!$i_cnt) {
            //一つもインスタンスない
            throw new \InvalidArgumentException();
        }
        if(is_null($key)) {
            if($i_cnt > 1) {
                //どれかワカンネ
                throw new \InvalidArgumentException();
            } else {
                reset($this->_instances);
                $key = key($this->_instances);
            }
        }
        $instance = isset($this->_instances[$key]) ? $this->_instances[$key] : null;
        if(is_null($instance)) {
            throw new \InvalidArgumentException();
        }
        return $instance->convertCarrier($text);
    }
}
