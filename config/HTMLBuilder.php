<?php

namespace config;

interface htmlComponents {
    public function setComponent($tag);
    public function setClass($class);
    public function setAttribute($attr, $value);
    public function setContent($content);
    public function setType($type);
    public function build();
}

interface htmlIncludes{
    public static function head();
    public static function script();
}

class HTMLBuilder implements htmlComponents, htmlIncludes{
    protected $tag;
    protected $attr; 
    protected $content;
    private static $dir;

    public function __construct()
    {
        $this->tag      = "";
        $this->attr     = [];
        $this->content  = "";
        list(, self::$dir) = explode('html', __DIR__, 2);
    }

    public static function head()
    {
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<link rel="stylesheet" href="config/Libraries/Bootstrap/dist/css/bootstrap.min.css">';
        echo '<link rel="stylesheet" href="config/Libraries/SweetAlert/dist/sweetalert2.min.css">';
        echo '<link rel="stylesheet" href="config/Libraries/DataTables/datatables.min.css">';
        echo '<link rel="stylesheet" href="config/Libraries/iziModal-master/css/iziModal.css">';
        echo '<link rel="stylesheet" href="/V4/Common Data/Libraries/Javascript/Bootstrap Multi-Select JS/dist/css/bootstrap-multiselect.css">';
        echo '<link href="/V4/Common Data/Templates/Bootstrap/js/bootstrap-toggle.min.css" rel="stylesheet">';
    }

    public static function script()
    {
        echo '<script src="config/Libraries/Bootstrap/dist/js/bootstrap.bundle.min.js"></script>'; // Includes Popper.js
        echo '<script src="config/Libraries/SweetAlert/dist/sweetalert2.min.js"></script>';
        echo '<script src="config/Libraries/DataTables/datatables.min.js"></script>';
        // echo '<script src="config/Libraries/jQuery/jquery3.7.1.js"></script>'; // jQuery first
        echo '<script src="config/Libraries/iziModal-master/js/iziModal.js"></script>'; 
        echo '<script src="config/Libraries/DataTables/Scroller-2.2.0/js/scroller.dataTables.min.js"></script>';
        echo '<script src="/V4/Common Data/Libraries/Javascript/Bootstrap Multi-Select JS/dist/js/bootstrap-multiselect.js"></script>';
        echo '<script src="/V4/Common Data/Templates/Bootstrap/js/bootstrap-toggle.min.js"></script>';
        echo '<script src="/V4/Common Data/Libraries/Javascript/localForage-1.10.0/dist/localforage.min.js"></script>';
    }
    
    public function setComponent($tag) {
        $this->tag = $tag;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setClass($class)
    {
        $this->attr['class'] = $class;
        return $this;
    }

    public function setAttribute($attr, $value)
    {
        $this->attr[$attr] = $value;
        return $this;
    }

    public function setType($type)
    {
        $this->attr['type'] = $type;
        return $this;
    }

    protected function renderAttributes()
    {
        $attrString = '';
        foreach ($this->attr as $attr => $value) {
            $attrString .= " $attr=\"$value\"";
        }
        return $attrString;
    }

    public function build()
    {
        return "<{$this->tag}{$this->renderAttributes()}>{$this->content}</{$this->tag}>";
    }
}

?>  