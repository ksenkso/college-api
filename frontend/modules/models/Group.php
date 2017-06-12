<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "group".
 *
 * @property integer $id
 * @property string $name
 * @property string $abbreviation
 * @property string $year
 * @property integer $spec_id
 */
class Group extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'abbreviation', 'year', 'spec_id'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['abbreviation'], 'string', 'max' => 10],
            [['year', 'spec_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'abbreviation' => 'Abbreviation',
            'year' => 'Year',
        ];
    }

	public function getSpec() {
		return $this->hasOne(Spec::className(), ['id' => 'spec_id']);
    }
}
