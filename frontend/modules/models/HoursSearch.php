<?php

namespace frontend\modules\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Hours;

/**
 * HoursSearch represents the model behind the search form about `frontend\models\Hours`.
 */
class HoursSearch extends Hours
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hours_id', 'student_id', 'group_id', 'hours_good'], 'integer'],
            [['date', 'hours'], 'safe'],
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

    public function getHoursOfGroup()
    {
        $query = Hours::find()->select(['']);
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
        $query = Hours::find();

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
            'hours_id' => $this->hours_id,
            'date' => $this->date,
            'student_id' => $this->student_id,
            'group_id' => $this->group_id,
            'hours_good' => $this->hours_good,
        ]);

        $query->andFilterWhere(['like', 'hours', $this->hours]);

        return $dataProvider;
    }
}
