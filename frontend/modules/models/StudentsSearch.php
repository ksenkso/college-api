<?php

namespace frontend\modules\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StudentsSearch represents the model behind the search form about `frontend\models\Students`.
 */
class StudentsSearch extends Students
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['student_id'], 'integer'],
            [['first_name', 'last_name', 'group_id'], 'safe'],
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
        $query = Students::find();

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
        Yii::trace(json_encode($this));
        $query->where(['group_id' => $this->group_id]);

        // grid filtering conditions
        $query->andFilterWhere([
            'student_id' => $this->student_id,
        ]);
        
        $query->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->orderBy('last_name');

        return $dataProvider;
    }
}
