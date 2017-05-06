<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "students".
 *
 * @property integer $student_id
 * @property string $first_name
 * @property string $last_name
 * @property integer $group_id
 *
 * @property Hours[] $hours
 * @property Today[] $todays
 */
class Students extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'students';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'group_id'], 'required'],
            [['group_id'], 'integer'],
            [['first_name', 'last_name', 'patronymic', 'address', 'phone'], 'string', 'max' => 80],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'student_id' => 'Идентификатор студента',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'group_id' => 'Группа',
        ];
    }

    /**
     * @param $group_id
     * @param $time
     * @return \yii\db\ActiveQuery
     */
    public function getHours($group_id = null, $time = null)
    {

        return $this->hasMany(Hours::className(), ['student_id' => 'student_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTodays()
    {
        return $this->hasMany(Today::className(), ['student_id' => 'student_id']);
    }
}
