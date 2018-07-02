<?php

namespace Lia\Media\Form;

use Lia\Form\Field;
use Lia\Form\Field\PlainInput;

class LFM extends Field
{
    use PlainInput;

    protected static $js = [
        '/vendor/laravel-filemanager/js/lfm.js',
    ];

    protected $type = "image";
    protected $prev = false;
    protected $max_width = "300px";

    protected $view = 'lia-media::lfm';

    public function max_width($width = 100)
    {
        $this->max_width = $width;
        return $this;
    }

    public function file()
    {
        $this->type = "file";
        return $this;
    }

    public function prev()
    {
        $this->prev = true;
        return $this;
    }

    public function render()
    {
        if(is_numeric($this->max_width)) $this->max_width .= "%";
        $this->initPlainInput();
        $name = $this->elementName ?: $this->formatName($this->column);

        $this->script = <<<SCRIPT

         $('.btn_{$name}').filemanager('{$this->type}', {prefix: '/admin/laravel-filemanager'});
         window.clear_{$name} = function(){ $('[name="{$this->column}"]').val(''); $('#prev_{$name}').attr('src',''); };
         $('[name="{$this->column}"]').on('keyup', function(){ $('#prev_{$name}').attr('src',$(this).val()); })

SCRIPT;

        $this->append('<a class="btn_'.$name.' btn btn-primary" href="#" data-input="'.$this->id.'" data-preview="prev_'.$name.'"><i class="fa fa-folder-o"></i></a>' )
            ->defaultAttribute('type', 'text')
            ->defaultAttribute('id', $this->id)
            ->defaultAttribute('name', $name)
            ->defaultAttribute('value', old($this->column, $this->value()))
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->defaultAttribute('placeholder', $this->getPlaceholder());


        return parent::render()->with([
            'prepend' => $this->prepend,
            'append'  => $this->append,
            'prev' => $this->prev,
            'name' => $name,
            'old_value' => old($this->column, $this->value()),
            'max_width' => $this->max_width
        ]);
    }
}