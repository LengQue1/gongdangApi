<?php
namespace App\Libraries\Service\Advertisement;

//广告
use App\Libraries\Service\BaseService;
use App\Libraries\Tool\DateTool;
use Exception;

class AdvertisementService
{
    //old查询
    public function get($id)
    {
        $advertisement = [];
        try {
            if (!empty($id)) {
                $baseService = new BaseService();
                $sql = "  select a.id,a.name,a.title,a.img_src,a.link,a.state,a.order_sort,a.create_date,
                a.category_id ,b.name as category_name from super_ads a left join super_ad_categorys b on a.category_id = b.id where a.id = $id";
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                if ($row = mysqli_fetch_array($result)) {
                    $advertisement['id'] = $row['id'];
                    $advertisement['title'] = $row['title'];
                    $advertisement['name'] = $row['name'];
                    $advertisement['link'] = $row['link'];
                    $advertisement['state'] = $row['state'];
                    $advertisement['imgUrl'] = $row['img_src'];
                    $advertisement['orderSort'] = $row['order_sort'];
                    $advertisement['categoryId'] = $row['category_id'];
                    $advertisement['categoryName'] = $row['category_name'];
                    $advertisement['createDate'] = $row['create_date'];
                    $advertisement['createDateShow'] = DateTool::strLongToDateByFormat('Y-m-d H:i:s', $row['create_date']);
                }
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $advertisement;
    }

    /**
     * @todo 列表
     * @param $params
     */
    public function getList($params)
    {
        $resultAry = [];
        $resultAry['list'] = [];
        $resultAry['count'] = 0;
        if (array_key_exists('pageNo', $params)) {
            $resultAry['pageNo'] = $params['pageNo'];
        }
        if (array_key_exists('pageSize', $params)) {
            $resultAry['pageSize'] = $params['pageSize'];
        }
        try {
            $index = 0;
            $baseService = new BaseService();
            $whereStr = $this->whereStr($params);
            $sql = "  select a.id,a.name,a.title,a.img_src,a.link,a.state,a.order_sort,a.create_date,
                a.category_id ,b.name as category_name from super_ads a left join super_ad_categorys b on a.category_id = b.id  ";
            $countSql = " select count(a.id) from super_ads a left join super_ad_categorys b on a.category_id = b.id " . $whereStr;
            //排序
            if (empty($params['sort'])) {
                $whereStr .= " order by a.order_sort asc";
            } else if($params['sort'] == 'orderSort'){
                $whereStr .= " order by a.order_sort " . (array_key_exists('order',$params)?$params['order']:"desc");
            }

            //分页参数
            if (!empty($params['max'])) {
                $whereStr .= "  limit   " . $params['offset'] . "," . $params['max'] . "";
            }
            $sql .= $whereStr;
            $conn = $baseService->dbConnection();
            $result = $conn->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $advertisement = [];
                $advertisement['id'] = $row['id'];
                $advertisement['title'] = $row['title'];
                $advertisement['name'] = $row['name'];
                $advertisement['link'] = $row['link'];
                $advertisement['state'] = $row['state'];
                $advertisement['imgUrl'] = $row['img_src'];
                $advertisement['orderSort'] = $row['order_sort'];
                $advertisement['categoryId'] = $row['category_id'];
                $advertisement['categoryName'] = $row['category_name'];
                $advertisement['createDate'] = $row['create_date'];
                $advertisement['createDateShow'] = DateTool::strLongToDateByFormat('Y-m-d H:i:s', $row['create_date']);
                array_push($resultAry['list'], $advertisement);
                $index++;
            }
            $resultAry['count'] = (int) mysqli_fetch_array($conn->query($countSql))[0];
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $resultAry;
    }

    public function latest($params)
    {
        $resultAry = [];
        try {
            $baseService = new BaseService();
            $sql = " select * FROM super_adsImg ORDER BY create_date DESC LIMIT 1 ";
            $conn = $baseService->dbConnection();
            $result = $conn->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $advertisement = [];
                $advertisement['id'] = $row['id'];
                $advertisement['version'] = $row['version'];
                $advertisement['url'] = $row['url'];
                $resultAry = $advertisement;
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $resultAry;
    }

    public function getImg($id)
    {
        $result = [];
        try {
            $baseService = new BaseService();
            $sql = " select * from super_adsImg where id = $id ";
            $conn = $baseService->dbConnection();
            $res = $conn->query($sql);
            while ($row = mysqli_fetch_array($res)) {
                $ads = [];
                $ads['id'] = $row['id'];
                $ads['version'] = $row['version'];
                $ads['url'] = $row['url'];
                $result = $ads;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $result;
    }

    //广告图片列表
    public function getImgList($params)
    {
        $resultAry = [];
        $resultAry['list'] = [];
        $resultAry['count'] = 0;
        if (array_key_exists('pageNo', $params)) {
            $resultAry['pageNo'] = $params['pageNo'];
        }
        if (array_key_exists('pageSize', $params)) {
            $resultAry['pageSize'] = $params['pageSize'];
        }
        try {
            $index = 0;
            $baseService = new BaseService();
            $whereStr =$this->whereStr($params);
            $sql = "  select id,version,url,create_date from super_adsImg ";
            $countSql = " select count(id) from super_adsImg ". $whereStr;

            //排序
            $whereStr .= " order by create_date desc";

            //分页参数
            if (!empty($params['max'])) {
                $whereStr .= "  limit   " . $params['offset'] . "," . $params['max'] . "";
            }
            $sql .= $whereStr;
            $conn = $baseService->dbConnection();
            $result = $conn->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $advertisement = [];
                $advertisement['id'] = $row['id'];
                $advertisement['version'] = $row['version'];
                $advertisement['imgUrl'] = $row['url'];
                $advertisement['createDate'] = $row['create_date'];
                $advertisement['createDateShow'] = DateTool::strLongToDateByFormat('Y-m-d H:i:s', $row['create_date']);
                array_push($resultAry['list'], $advertisement);
                $index++;
            }
            $resultAry['count'] = (int) mysqli_fetch_array($conn->query($countSql))[0];
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $resultAry;
    }

    //拼装查询条件
    public function whereStr($params)
    {
        $whereStr = " where 1=1 ";

        if (!empty($params['version'])) {
            $whereStr .= " and version like '%" . $params['version'] . "%'";
        }
        if (!empty($params['url'])) {
            $whereStr .= " and url like '%" . $params['url'] . "%'";
        }

        return $whereStr;
    }

    //新增
    public function save($params)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            if (empty($params['name'])) {
                $params['state'] = '';
            }
            if (empty($params['link'])) {
                $params['link'] = '';
            }
            if (empty($params['title'])) {
                $params['title'] = '';
            }
            if (empty($params['state'])) {
                $params['state'] = 0;
            }
            if (empty($params['orderSort'])) {
                $params['orderSort'] = 0;
            }
            if (empty($params['categoryId'])) {
                $params['categoryId'] = 'null';
            }
            if (empty($params['imgUrl'])) {
                $params['imgUrl'] = '';
            }
            $params['createDate'] = DateTool::getMillisecond();
            $sql = " insert into super_ads(name,img_src,link,state,create_date,order_sort,category_id,title) values('" . $params["name"]
                . "','" . $params["imgUrl"] . "','" . $params["link"] . "'," . $params["state"] . "," . $params["createDate"] . "," . $params["orderSort"] .
                "," . $params["categoryId"] . ",'" . $params["title"] . "')";
            $conn = $baseService->dbConnection();
            if ($conn->query($sql)) {
                $flag = true;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }

    //更新
    public function update($params)
    {
        $flag = false;$paramArray = [];$types = "";
        try {
            if (!empty($params['id'])) {
                $baseService = new BaseService();
                $setSql = " set ";

                if (isset($params['title'])) {
                    $setSql .= "title = ?,";
                    $types .= "s";
                    array_push($paramArray,$params['title']);
                }
                if (isset($params['link'])) {
                    $setSql .= "link = ?,";
                    $types .= "s";
                    array_push($paramArray,$params['link']);
                }
                if (isset($params['imgUrl'])) {
                    $setSql .= "img_src = ?,";
                    $types .= "s";
                    array_push($paramArray,$params['imgUrl']);
                }
                if (isset($params['state'])) {
                    $setSql .= "state = ?,";
                    $types .= "i";
                    array_push($paramArray,$params['state']);
                }
                if (isset($params['name'])) {
                    $setSql .= "name = ?,";
                    $types .= "s";
                    array_push($paramArray,$params['name']);
                }
                if (isset($params['orderSort'])) {
                    $setSql .= "order_sort = ?,";
                    $types .= "i";
                    array_push($paramArray,$params['orderSort']);
                }
                if (array_key_exists('categoryId',$params)) {
                    $setSql .= "category_id = ?,";
                    $types .= "i";
                    if (empty($params['categoryId'])) {
                        array_push($paramArray,null);
                    }else{
                        array_push($paramArray,$params['categoryId']);
                    }
                }
                $setSql = substr($setSql,0,strlen($setSql)-1);
                $whereSql = " where id=" . $params['id'];
                $sql = "update super_ads " . $setSql . $whereSql;
                $conn = $baseService->dbConnection();
                $stmt = $conn->prepare($sql);
                $baseService->bindParam($stmt,$types,$paramArray);
                if ($stmt->execute() === true) {
                    $flag = true;
                }
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }

    //保存广告图片
    public function saveImg($params)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            $params['create_date'] = DateTool::getMillisecond();
            $sql = " insert into super_adsImg(version,url,create_date) values('" . $params["version"] . "','" . $params["url"] . "','" . $params["create_date"] . "')";
            $conn = $baseService->dbConnection();
            if ($conn->query($sql)) {
                $flag = true;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }

    //删除
    public function del($id)
    {
        $flag = false;
        try {
            if (!empty($id)) {
                $baseService = new BaseService();
                $sql = "delete from super_adsImg where id=" . $id;
                $conn = $baseService->dbConnection();
                if ($conn->query($sql)) {
                    $flag = true;
                }
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }
}