<?php

namespace Lia\Media\Types;

use Illuminate\Http\Request;
use Lia\Form;
use Intervention\Image\ImageManagerStatic;
use Lia\Media\Database\LiaMedia;

class ImgType {

    public static function form(Form $form)
    {
        $form->lfm('data', 'Image')->prev()->rules('required');
        $form->text('title')->rules('required');
        $form->ckeditor('description');

        return $form;
    }

    public static function save(Form $form, $data, Request $request)
    {
        $id = $request->lia_media ? $request->lia_media : false;

        if(isset($data['data'])) $data['preview'] = $data['data'];

        if($id) $result = LiaMedia::find($id)->update($data);
        else $result = LiaMedia::create($data);

        if($result)
            return ['status' => 'success', 'title' => 'Image has be saved!'];
        else
            return ['status' => 'error', 'title' => 'Undefined error'];
    }

}