<?php

class News extends CActiveRecord 
{
    public $img;

    public function __construct(){
        parent::__construct();
    }
 
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, alias', 'required'),
            array('name, alias, author', 'length', 'max'=>255),
            array('category, status, body, tags, alias, sdate, popular, media, intro', 'safe'),
            array('img', 'imageValidate',
                    'types'=>array('jpg', 'jpeg', 'gif', 'png'), 
                    'mimeTypes' => array('image/jpeg', 'image/png', 'image/gif'),
                    'maxWidth'=>800,
                    'maxHeight'=>800,
                    'postFieldName'=>'image', // Form field
                 ),
            // The following rule is used by search().
            array('id, name, tags, alias', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'category' => Yii::t('adminModule.app','Категория'),
            'name' => Yii::t('adminModule.app','Название'),
            'alias' => Yii::t('adminModule.app','Алиас'),
            'media' => Yii::t('adminModule.app','Медиа контент'),
            'intro' => Yii::t('adminModule.app','Интро'),
            'body' => Yii::t('adminModule.app','Текст'),
            'sdate' => Yii::t('adminModule.app','Дата'),
            'img' => Yii::t('adminModule.app','Изображение'),
            'tags' => Yii::t('adminModule.app','Теги'),
            'author' => Yii::t('adminModule.app','Автор'),
            'popular' => Yii::t('adminModule.app','Популярно'),
            'status' => Yii::t('adminModule.app','Вкл.'),

        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        /* SEARCH PART */
        $searchColumns = array(
            'id',
            'name',
            // 'sdate', //data is out of LIKE search
            'tags',
        );

        $criteria = new CDbCriteria;
        if (isset($_GET['search']))
        {
            $criteria->condition = '';
            $i = 0;
            foreach ($searchColumns as $column)
            {
                if($i == 0)
                    $criteria->condition = $column.' LIKE :'.$column.' ';                
                else
                    $criteria->condition .= ' OR '.$column.' LIKE :'.$column.' ';                

                $criteria->params[':'.$column] = '%'.$_GET['search'].'%';
                $i++;
            }
        }

        /* SORT PART */
        $criteria->order = "id DESC"; //defalut order
        if (isset($_GET['sort']))
        {        
            if(isset($_GET['howsort']) && $_GET['howsort'] == 'asc')
                $criteria->order = $_GET['sort'].' ASC';
            else
                $criteria->order = $_GET['sort'].' DESC';
        }

        return new CActiveDataProvider($this, array(
            'pagination'=>array('pageSize'=>10),
            'criteria'=>$criteria,
        ));
    }

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return Yii::app()->db->tablePrefix.'news';
    }

    /**
     * delete Image from HDD
     */    
    public function delOldImgFile($image = null){
        if(is_file(Yii::app()->basePath . '/../uploads/' . strtolower(get_class($this)) . '/' . $image))
            unlink(Yii::app()->basePath . '/../uploads/' . strtolower(get_class($this)) . '/' . $image);
        return true;
    }

    /**
     * validate Image on upload
     */    
    public function imageValidate($attribute, $params)
    {
        if (isset($_FILES[get_class($this)]['tmp_name'][$params['postFieldName']]) 
            && 
            $_FILES[get_class($this)]['tmp_name'][$params['postFieldName']] != '')
        {
            $imagehw = getimagesize($_FILES[get_class($this)]['tmp_name']['image']);
            $imageWidth  = $imagehw[0];
            $imageHeight = $imagehw[1];
            $imageMType  = $imagehw['mime'];

            if (in_array($imageMType, $params['mimeTypes']))
            {
                if($imageWidth < $params['maxWidth'] || $imageHeight < $params['maxHeight'] )
                    return true;
                else
                    $this->addError($attribute, 'Размер больше '.$params['maxWidth'].'px * '.$params['maxHeight'].'px');
            }
            else
                $this->addError($attribute, 'Неверный формат файла');

            return false;
        }
    }

    // protected function beforeSave() 
    // {
    //     return parent::beforeSave();
    // }
}