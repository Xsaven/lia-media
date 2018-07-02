<?php

namespace Lia\Media\Types;

use Illuminate\Http\Request;
use Lia\Form;
use Lia\Media\Database\LiaMedia;

class VideoType {

    public static function form(Form $form, $id)
    {
        $form->text('data', 'YouTube code')->help('https://www.youtube.com/watch?v=<b>s1Qe23hNw0</b>');

        if($id) {
            $form->lfm('preview')->prev();
            $form->text('title');
        }

        $form->ckeditor('description');

        return $form;
    }

    public static function save(Form $form, $data, Request $request)
    {
        $id = $request->lia_media ? $request->lia_media : false;

        if(!$id) {
            $content = file_get_contents("http://youtube.com/get_video_info?video_id=" . $data['data']);
            parse_str($content, $ytarr);
            $data['title'] = $ytarr['title'];
            $data['preview'] = "https://img.youtube.com/vi/{$data['data']}/0.jpg";
        }

        if($id) $result = LiaMedia::find($id)->update($data);
        else $result = LiaMedia::create($data);

        if($result)
            return ['status' => 'success', 'title' => 'Video has be saved!'];
        else
            return ['status' => 'error', 'title' => 'Undefined error'];
    }

}