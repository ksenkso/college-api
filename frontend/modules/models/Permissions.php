<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 03.10.2016
 * Time: 17:38
 */

namespace frontend\modules\models;


use yii\base\Model;

class Permissions extends Model
{

    public $userId;
    public $isAdmin;
    public $isSteward;
    public $isStudent;

    public function rules()
    {
        return [
            [['userId', 'isAdmin','isSteward', 'isStudent'], 'required'],
            [['isAdmin'], 'boolean'],
            [['isSteward'], 'boolean'],
            [['isStudent'], 'boolean']
        ];
    }

    public function update()
    {
        if ($this->validate())
        {
            $auth = \Yii::$app->authManager;
            $roles = [];
            if ($this->isAdmin)
            {
                $roles[] = 'admin';
            }
            
            if ($this->isSteward)
            {
                $roles[] = 'steward';
            }
            
           if ($this->isStudent)
           {
               $roles[] = 'student';
           }
            
            if (count($roles))
            {
                $auth->revokeAll($this->userId);
                
                foreach ($roles as $role) {
                    $auth->assign($auth->getRole($role), $this->userId);    
                }
            } else return null;
            
            return true;
        } else return null;
    }
    
}