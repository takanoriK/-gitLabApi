<?php

$exec = new GetGitLabApiLabels();
$exec->main();


class GetGitLabApiLabels {
    const PRIVATE_TOKEN = 'mytoken';

    static $value = array();

    /**
     * 未レビューのMRを取得する
     */
    public function main(){
        $url_first = 'https://[GitLabのHost]/api/v4/projects/';

        //サービスID
        $url_id = array('projectname' => 'projectID',
                    );
        
        //未レビューというラベルが付いているMRのみ取得
        $url_after = '/merge_requests?state=opened&labels=%E6%9C%AA%E3%83%AC%E3%83%93%E3%83%A5%E3%83%BC&private_token='.self::PRIVATE_TOKEN;

        //gitLabAPIからデータを取得
        $get_datas = $this->getGitLabApi($url_first, $url_id, $url_after);
        foreach ($get_datas as $contents => $requests){
            foreach($requests as $data){
                $this->setData($contents, $data);
            }
        }

        //正常終了
        $this->value['status'] = 200;
        $encode =json_encode($this->value);
        return $encode;
    }

    /**
     * gitLabApi取得
     * @param string $url_first
     * @param array $url_id
     * @param string $url_after
     *
     * @return array $encode_results
     */
    private function getGitLabApi($url_first, $url_id, $url_after){
        $ch = curl_init();
        $decode_results = array();
        foreach ($url_id as $contents => $project_id) {
            $culr_url = $url_first . $project_id . $url_after;
            curl_setopt($ch, CURLOPT_URL, $culr_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            if($result != '[]'){
                $decode_results[$contents] = json_decode($result, true);
            }
        }
        curl_close($ch);
        return $decode_results;
    }

    /**
     * タイトルとレビュー担当をセット
     * @param string $contents
     * @param array $data
     */
    private function setData($contents, $data){
        if(isset($data['title']) && isset($data['labels'])){
            $this->value['responce'][$contents]['title'] = $data['title'];
            $this->value['responce'][$contents]['url'] = $data['web_url'];
            $this->setTimeLimit($contents, $data['title']);
            $this->value['responce'][$contents]['labels']['name1'] = $data['labels'][0];

            //アサインが2人の場合
            if(isset($data['title'][1]) && $data['title'][1] != '未レビュー'){
                $this->value['responce'][$contents]['labels']['name2'] = $data['labels'][1];
            }
        }
    }

    /**
     * タイトルの期限を切り出して配列にセットする
     * @param string $contents
     * @param string $title
     */
    private function setTimeLimit($contents, $title){
        $this->value['responce'][$contents]['limit'] = mb_substr($title, 8, 5,'UTF-8');
    }
}