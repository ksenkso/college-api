<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "group".
 *
 * @property integer $group_id
 * @property string $group_name
 * @property string $abbreviation
 * @property string $year
 * @property string $group_number
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
            [['group_name', 'abbreviation', 'year'], 'required'],
            [['group_name'], 'string', 'max' => 255],
            [['abbreviation'], 'string', 'max' => 10],
            [['year', 'group_number'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'group_id' => 'Идентификатор группы',
            'group_name' => 'Название группы',
            'abbreviation' => 'Аббревиатура',
            'year' => 'Год поступления',
            'group_number' => 'Номер группы на курсе',
        ];
    }

    public function getFullAbbreviation()
    {
        return $this->abbreviation . " " . $this->year . "." . $this->group_number;
    }
}
