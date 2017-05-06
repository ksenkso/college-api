<?php

namespace frontend\modules\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Event;

/**
 * EventSearch represents the model behind the search form about `frontend\models\Event`.
 */
class EventSearch extends Event
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_id', 'user_id', 'e_type_id'], 'integer'],
            [['caption', 'description', 'start_time', 'start_date', 'end_time', 'end_date'], 'safe'],
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
        $query = Event::find();

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
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'e_type_id' => $this->e_type_id,
            'start_time' => $this->start_time,
            'start_date' => $this->start_date,
            'end_time' => $this->end_time,
            'end_date' => $this->end_date,
        ]);

        $query->andFilterWhere(['like', 'caption', $this->caption])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
