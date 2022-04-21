<?php

namespace App\Plugins\Dakoku;

use Exceedone\Exment\Services\Plugin\PluginDashboardBase;
use Exceedone\Exment\Model\CustomTable;

class Plugin extends PluginDashboardBase
{
    /**
     *
     * @return Content|\Illuminate\Http\Response
     */
    public function body()
    {
        if(is_null(CustomTable::getEloquent('dakoku'))){
            return '打刻テーブルがインストールされていません。';
        }
        
        $dakoku = $this->getDakoku();
        $status = $this->getStatus($dakoku);
        $params = $this->getStatusParams($status);

        return view('exment_dakoku::dakoku', [
            'dakoku' => $dakoku,
            'params' => $params,
            'action' => admin_url($this->getDashboardUri('post')),
        ]);
    }

    /**
     * 送信
     *
     * @return void
     */
    public function post()
    {
        $dakoku = $this->getDakoku();
        
        $now = \Carbon\Carbon::now();

        if(!isset($dakoku)){
            $dakoku = CustomTable::getEloquent('dakoku')->getValueModel();
            $dakoku->setValue('target_date', $now->toDateString());
        }

        $status = null;
        switch(request()->get('action')){
            // 出勤
            case 'syukkin':
                $dakoku->setValue('syukkin_time', $now);
                $status = 1;
                break;
            // 休憩開始
            case 'kyuukei_start':
                $dakoku->setValue('kyuukei_start_time', $now);
                $status = 11;
                break;
            // 休憩終了
            case 'kyuukei_end':
                $dakoku->setValue('kyuukei_end_time', $now);
                $status = 21;
                break;
            // 退勤
            case 'taikin':
                $dakoku->setValue('taikin_time', $now);
                $status = 99;
                break;
        }
        if(isset($status)){
            $dakoku->setValue('status', $status);
        }
        $dakoku->save();

        admin_toastr(trans('admin.save_succeeded'));
        return back();
    }

    /**
     * 現在の勤怠状況を取得
     *
     * @return void
     */
    protected function getDakoku(){
        $table = CustomTable::getEloquent('dakoku');
        if(!isset($table)){
            return null;
        }
        
        
        // 現在時刻を取得
        $now = \Carbon\Carbon::now();

        // 基準時間より前であれば、前日として扱う
        if($now->hour <= 4){
            $now = $now->addDay(-1);
        }

        // 該当の打刻を取得
        $query = getModelName('dakoku')::query();
        $query->where('value->target_date', $now->toDateString())
            ->where('created_user_id', \Exment::user()->base_user_id);

        $dakoku = $query->first();

        return $dakoku;
    }

    /**
     * ステータスを取得
     *
     * @param [type] $dakoku
     * @return int
     */
    protected function getStatus($dakoku){
        if(!isset($dakoku) || is_null($dakoku->getValue('syukkin_time'))){
            return 0;
        }

        return $dakoku->getValue('status');
    }

    protected function getStatusParams($status){
        switch($status){
            case 0:
                return 
                [
                    'status_text' => '未出勤',
                    'buttons' => [
                        [
                        'button_text' => '出勤',
                        'action_name' => 'syukkin',
                        ]
                    ]
                ];
            // 出勤
            case 1:
                return  
                [
                    'status_text' => '出勤中',
                    'buttons' => [
                        [
                            'button_text' => '休憩',
                            'action_name' => 'kyuukei_start',
                        ], 
                        [
                            'button_text' => '退勤',
                            'action_name' => 'taikin',
                        ], 
                    ]
                ];
            case 11:
                return 
                [
                    'status_text' => '休憩中',
                    'buttons' => [
                        [
                            'button_text' => '休憩終了',
                            'action_name' => 'kyuukei_end',
                        ], 
                    ]
                ];
            case 21:
                return 
                [
                    'status_text' => '出勤中',
                    'buttons' => [
                        [
                            'button_text' => '退勤',
                            'action_name' => 'taikin',
                        ], 
                    ]
                ]; 
                
            case 99:
                return 
                [
                    'status_text' => '退勤',
                    'buttons' => [
                    ]
                ];
        }
    }
}