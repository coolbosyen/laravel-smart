<?php namespace Smart\Service;
/**
 * MerAlbum Service
 *
 * @author MR.Z <zsh2088@gmail.com>
 * @version 2.0 2017-09-26
 */

use Facades\Smart\Service\ServiceManager;
use Smart\Models\MerAlbum;
use Smart\Models\MerAlbumCatalog;

class MerAlbumService extends BaseService {

  	//引入 GridTable trait
	use \Smart\Traits\Service\GridTable;

    public $albumCatalog;

    public $albumTag;

  //状态
	public $status = [
		0 => '禁用',
		1 => '启用',
	];

    public function __construct()
    {
        $this->albumCatalog = ServiceManager::make( MerAlbumCatalogService::class );
        $this->albumTag = ServiceManager::make( MerAlbumTagService::class );
    }

    //类实例
	private static $instance;

	//生成类单例
	public static function instance() {
		if ( self::$instance == NULL ) {
			self::$instance = new MerAlbumService();
			self::$instance->setModel(new MerAlbum());
		}


		return self::$instance;
	}

  //取默认值
	function getDefaultRow() {
		return [
			'id' => '' , 
'mer_id' => '' , 
'sort' => '999' , 
'uri' => '' , 
'size' => '' , 
'mimes' => '' , 
'img_size' => '' , 
'desc' => '' , 
'status' => '1' , 
'created_at' => date('Y-m-d H:i:s') , 
		];
	}

  /**
 * 根据条件查询
 *
 * @param $param
 *
 * @return array|number
 */
public function getByCond( $param ) {
  $default = [
    'field'    => [ '*'],
    'keyword'  => '',
    'status'   => '',
    'page'     => 1,
    'pageSize' => 10,
    'sort'     => 'id',
    'order'    => 'DESC',
    'count'    => FALSE,
    'getAll'   => FALSE
  ];

  $param = extend( $default, $param );

  $model = $this->getModel()->keyword($param['keyword'])->status($param['status']);

  if ( $param['count'] ) {
    return $model->count();
  }


   $data =  $model->getAll($param)->orderBy($param['sort'], $param['order'])->get($param['field'])->toArray();

  return $data ? $data : [ ];
}

    public function insert( $data, $tags = '' ){

        $mer_id = isset( $data['mer_id'] ) ? $data['mer_id'] : '';

        if ( empty( $mer_id ) ) {
            unset( $data['mer_id'] );
        }
        $id = $this->getmodel()->save( $data );

        $tags     = empty( $tags ) ? '默认相册' : $tags;
        if ( ! is_array( $tags ) ) {
            $tags = explode( ',', trim( $tags ) );
        }
        $data_tag  = [];
        /*foreach( $tags as $tag){
            array_push( $data_tag , new MerAlbumCatalog($tag));
        }*/
        $ret_save = $this->getmodel()->tag()->save($data_tag);
        //saveByTags( $mer_id, $tags, $id, $data['uri'] );

        if ( $ret_save['code'] != 0 ) {
            throw new \Exception( $ret_save['msg'] );
        }



            return ajax_arr( '添加成功', 0, [ 'id' => $id ] );

    }

}