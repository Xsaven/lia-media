<?php

namespace Lia\Media\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lia\Controllers\ModelForm;
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

            $descr = 'список';
            $title = 'Media';
            $relate = config('lia-media.relate');

            if(request()->relate_id && $relate['model']){
                if($r = $relate['model']::find(request()->relate_id))
                    $descr = $r->{$relate['title_filed']};
            }

            if($relate['model']) $title = $relate['name'].' '.$title;

            $content->header($title);
            $content->description($descr);
            $content->body($this->grid());
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {

            $descr = 'Create';
            $title = 'Media';
            $relate = config('lia-media.relate');

            if(request()->relate_id && $relate['model']){
                if($r = $relate['model']::find(request()->relate_id))
                    $descr .= ' for "'.$r->{$relate['title_filed']}.'"';
            }

            if($relate['model']) $title = $relate['name'].' '.$title;

            $content->header($title);
            $content->description($descr);

            $content->body($this->form());
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $descr = 'Edit';
            $title = 'Media';
            $relate = config('lia-media.relate');

            if(request()->relate_id && $relate['model']){
                if($r = $relate['model']::find(request()->relate_id))
                    $descr .= ' for "'.$r->{$relate['title_filed']}.'"';
            }

            if($relate['model']) $title = $relate['name'].' '.$title;

            $content->header($title);
            $content->description($descr);

            $content->body($this->form($id)->edit($id));
        });
    }

    protected function grid()
    {
        return Admin::grid(LiaMedia::class, function (Grid $grid) {

            $relate = config('lia-media.relate');
            $tools = [];
            foreach(config('lia-media.types') as $type => $tool){
                $params = ['type' => $type];
                if($relate['model'] && request()->relate_id) $params['relate_id'] = request()->relate_id;
                $tools[] = '<a href="'.route('lia_media.create', $params).'" class="btn btn-sm btn-success"><i class="fa fa-'.$tool['icon'].'"></i>&nbsp;&nbsp;New '.$tool['title'].'</a>';
            }

            $grid->addTool($tools);

            $grid->disableExport();
            $grid->disableCreation();

            $grid->id('ID')->sortable();

            $grid->column('preview')->display(function($src){
                return "<img src='{$src}' width='50px' />";
            });

            $grid->column('type', 'Type')->display(function($type){
                return config('lia-media.types.'.$type.'.title');
            })->badge('blue');

            $grid->title('Название')->sortable();

            foreach(config('lia-media.markers') as $key => $data) {
                if (isset($data['grid']) && $data['grid']){
                    if($data['form']['type']=='switch') $grid->{$key}()->switch()->sortable();
                }
            }

            $grid->active()->switch()->sortable();

            $grid->created_at();
            $grid->updated_at();

            $grid->filter(function($filter){
                $relate = config('lia-media.relate');
                if($relate['model'])
                    $filter->equal('relate_id', $relate['name'])->select($relate['model']::all()->pluck($relate['title_filed'], $relate['id_filed']));
                $types = [];
                foreach (config('lia-media.types') as $key => $type)
                    $types[$key] = $type['title'];

                $filter->equal('type', "Type")->select($types);
            });
        });
    }

    protected function form($id=false)
    {
        $type = request()->type ? request()->type : (request()->lia_media ? LiaMedia::find(request()->lia_media)->type : false);
        if(!$type) abort(404);
        if(request()->relate_id) session(['relate_id' => request()->relate_id]);

        return Admin::form(LiaMedia::class, function (Form $form) use ($id, $type) {

            if($id) $form->hidden('media_id', $id);

            $types = config('lia-media.types');

            if(!isset($types[$type])) throw new \Exception('Type "'.$type.'" not found!');

            $form->hidden('type')->default($type);

            $relate_model = config('lia-media.relate.model');
            if($relate_model) {
                $relate_all = $relate_model::all()->pluck(config('lia-media.relate.title_filed'), config('lia-media.relate.id_filed'));
                $form->select('relate_id', config('lia-media.relate.name'))->options($relate_all)->rules('required')->default(request()->relate_id ? request()->relate_id : 0);
            }

            $form = $types[$type]['class']::form($form, $id);

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
                        else {
                            $r_id = session('relate_id');
                            if($r_id) session(['relate_id' => null]);
                            return redirect()->route('lia_media.index', $r_id ? ['relate_id' => $r_id] : [])->with(compact($status));
                        }
                    }else
                        return back();
                }else
                    throw new \Exception('Not fount "Save" event!');
            });

            $form->switch('active')->default(1);
        });
    }

}