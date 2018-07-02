<?php

namespace Lia\Media\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lia\Controllers\ModelForm;
use Lia\Exception\Handler;
use Lia\Facades\Admin;
use Lia\Layout\Content;
use Lia\Grid;
use Lia\Media\Database\LiaMedia;
use Lia\Form;
use Illuminate\Support\MessageBag;

class MediaController extends Controller{

    use ModelForm;

    public function index(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('Media');
            $content->description('список');
            $content->body($this->grid());
        });
    }

    protected function grid()
    {
        return Admin::grid(LiaMedia::class, function (Grid $grid) {

            $tools = [];
            foreach(config('lia-media.types') as $type => $tool){
                $tools[] = '<a href="'.route('lia_media.create', ['type' => $type]).'" class="btn btn-sm btn-success"><i class="fa fa-'.$tool['icon'].'"></i>&nbsp;&nbsp;New '.$tool['title'].'</a>';
            }

            $grid->addTool($tools);

            $grid->disableExport();
            $grid->disableCreation();

            $grid->id('ID')->sortable();

            $grid->column('preview')->display(function($src){
                return "<img src='{$src}' width='50px' />";
            });

            $grid->type('Type')->badge();
            $grid->title('Название')->sortable();

            foreach(config('lia-media.markers') as $key => $data) {
                if (isset($data['grid']) && $data['grid']){
                    if($data['form']['type']=='switch') $grid->{$key}()->switch()->sortable();
                }
            }

            $grid->active()->switch()->sortable();

            $grid->created_at();
            $grid->updated_at();
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Media');
            $content->description('добавить');

            $content->body($this->form());
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Media');
            $content->description('редактировать');

            $content->body($this->form($id)->edit($id));
        });
    }

    protected function form($id=false)
    {

        if(request()->isMethod('GET') && !request()->type) abort(404);
        $type = request()->type ? request()->type : (request()->lia_media ? LiaMedia::find(request()->lia_media)->type : false);
        if(!$type) abort(404);

        return Admin::form(LiaMedia::class, function (Form $form) use ($id, $type) {

            if($id) $form->hidden('product_id', $id);

            $types = config('lia-media.types');

            if(!isset($types[$type])) throw new \Exception('Type "'.$type.'" not found!');

            $form->hidden('type')->default($type);

            $relate_model = config('lia-media.relate.model');
            if($relate_model) {
                $relate_all = $relate_model::all()->pluck(config('lia-media.relate.title_filed'), config('lia-media.relate.id_filed'));
                $form->select('relate_id', config('lia-media.relate.name'))->options($relate_all)->rules('required');
            }

            $form = $types[$type]['class']::form($form);

            foreach(config('lia-media.markers') as $key => $data) {
                if (isset($data['grid']) && $data['grid']){
                    $form->{$data['form']['type']}($key)->default($data['form']['default']);
                }
            }

            $form->saving(function (Form $form) use ( $types, $type ) {
                if(is_callable($types[$type]['class'], 'save')) {
                    $data = [];
                    foreach(request()->all() as $key => $val) {
                        if($val == 'on' || $val == 'off') $val = $val == 'on' ? 1 : 0;
                        $data[$key] = $val;
                    }
                    $result = $types[$type]['class']::save($form, $data, request());
                    if(isset($result['status'])){
                        $status = $result['status'];
                        ${$status} = new MessageBag([
                            'title'   => isset($result['title']) ? $result['title'] : '',
                            'message' => isset($result['message']) ? $result['message'] : '',
                        ]);
                        if(isset($result['redirect_name']))
                            return redirect()->route($result['redirect_name'])->with(compact($status));
                        else if(isset($result['redirect']))
                            return redirect($result['redirect'])->with(compact($status));
                        else
                            return redirect()->route('lia_media.index')->with(compact($status));
                    }else
                        return back();
                }else
                    throw new \Exception('Not fount "Save" event!');
            });

            $form->switch('active')->default(1);
        });
    }

}