<?php

namespace frontend\modules\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Notification;

/**
 * NotificationSearch represents the model behind the search form about `frontend\models\Notification`.
 */
class NotificationSearch extends Notification
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notification_id', 'type', 'from', 'to', 'is_read'], 'integer'],
            [['target', 'ts'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Notification::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'notification_id' => $this->notification_id,
            'type' => $this->type,
            'from' => $this->from,
            'to' => $this->to,
            'is_read' => $this->is_read,
            'ts' => $this->ts,
        ]);

        $query->andFilterWhere(['like', 'target', $this->target]);

        return $dataProvider;
    }
}
