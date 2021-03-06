<?php

namespace App\Controller;

use App\Core\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListController extends Controller
{
    public function fetchClient(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT tclientes.CLIEV_CODIEMPR,tclientes.CLIEV_IDCLIENTE,tclientes.CLIEV_RIF,tclientes.CLIEV_RAZONSOC,tclientes.CLIEV_NOMBRE,
          tplanrutas.PLANV_TIPOPRECIO,tplanrutas.PLANV_IDVENDEDOR,tclientes.CLIEV_IDTIPO FROM tclientes 
          INNER JOIN tplanrutas ON tclientes.CLIEV_CODIEMPR = tplanrutas.PLANV_CODIEMPR AND tclientes.CLIEV_IDCLIENTE = tplanrutas.PLANV_IDCLIENTE
          INNER JOIN tcuentas_cli ON tplanrutas.PLANV_CODIEMPR = tcuentas_cli.CRCV_CODIEMPR AND tplanrutas.PLANV_IDCLIENTE = tcuentas_cli.CRCV_IDCLIENTE  
          WHERE tclientes.CLIEV_CODIEMPR = :idempresa AND tplanrutas.PLANV_IDCLIENTE = :idcliente AND tplanrutas.PLANV_IDVENDEDOR = :idvendedor");
        $stmt->bindParam("idempresa",$_SESSION["user"]["details"]["CRCV_CODIEMPR"]);
        $stmt->bindParam("idcliente",$_SESSION["user"]["details"]["CRCV_IDCLIENTE"]);
        $stmt->bindParam("idvendedor",$_SESSION["user"]["details"]["CRCV_IDVENDEDOR"]);
        $stmt->execute();
        $data = $stmt->fetch();       
        /*return $response->withJson(array(
            "id" => $_SESSION["user"]["details"]["CRCV_IDVENDEDOR"]
          )); */

            $data['sesion']=$_SESSION["user"]["CUENV_TIPO"];
        return $response->withJson($data);
    }

    public function fetchClients(Request $request, Response $response, $args)
    {
        
        $sSelect = "vacio";
        if($_SESSION["user"]["CUENV_TIPO"] != 0){
          $sSelect = "SELECT tclientes.CLIEV_CODIEMPR,tclientes.CLIEV_TELEFONO2,tclientes.CLIEV_MOVIL,tclientes.CLIEV_IDCLIENTE,tclientes.CLIEV_RIF,tclientes.CLIEV_RAZONSOC,tclientes.CLIEV_NOMBRE,
                        tplanrutas.PLANV_TIPOPRECIO,tcuentas_ven.CRVV_IDVENDEDOR,tclientes.CLIEV_IDTIPO FROM tclientes
                        INNER JOIN tplanrutas ON tclientes.CLIEV_CODIEMPR = tplanrutas.PLANV_CODIEMPR AND tclientes.CLIEV_IDCLIENTE = tplanrutas.PLANV_IDCLIENTE
                        INNER JOIN tcuentas_ven ON tplanrutas.PLANV_CODIEMPR = tcuentas_ven.CRVV_CODIEMPR AND tplanrutas.PLANV_IDVENDEDOR = tcuentas_ven.CRVV_IDVENDEDOR";
          $sSelect .= " WHERE tclientes.CLIEV_CODIEMPR = '".$_SESSION["user"]["details"]["CRVV_CODIEMPR"]."'";
          $sSelect .= " AND tplanrutas.PLANV_IDVENDEDOR = '".$_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]."'";
          $sSelect .= " GROUP BY tclientes.CLIEV_CODIEMPR,tclientes.CLIEV_IDCLIENTE,tplanrutas.PLANV_TIPOPRECIO";
        }else{
          $sSelect = "SELECT tclientes.CLIEV_CODIEMPR,tclientes.CLIEV_TELEFONO2,tclientes.CLIEV_MOVIL,tclientes.CLIEV_IDCLIENTE,tclientes.CLIEV_RIF,tclientes.CLIEV_RAZONSOC,tclientes.CLIEV_NOMBRE,
                        tplanrutas.PLANV_TIPOPRECIO,tplanrutas.PLANV_IDVENDEDOR as CRVV_IDVENDEDOR,tclientes.CLIEV_IDTIPO  FROM tclientes
                      INNER JOIN tplanrutas ON tclientes.CLIEV_CODIEMPR = tplanrutas.PLANV_CODIEMPR AND tclientes.CLIEV_IDCLIENTE = tplanrutas.PLANV_IDCLIENTE
                      GROUP BY tclientes.CLIEV_CODIEMPR,tclientes.CLIEV_IDCLIENTE";
                      //$sSelect .= "WHERE tclientes.CLIEV_CODIEMPR = '".$_SESSION["user"]["details"]["CRVV_CODIEMPR"]."'";
        }
        
        $stmt = $this->db->prepare($sSelect); 
        $stmt->execute();
        $data = $stmt->fetchAll();
        for($i=0;$i<count($data);$i++)
            $data[$i]['sesion']=$_SESSION["user"]["CUENV_TIPO"];
        return $response->withJson($data);
    }

    public function fetchSeller(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT VENDV_IDVENDEDOR, VENDV_NOMBRE, c.CRVV_CODIEMPR FROM tvendedores v INNER JOIN tcuentas_ven c ON v.VENDV_CODIEMPR = c.CRVV_CODIEMPR
                                     WHERE c.CRVN_IDCUENTA = :idcuenta LIMIT 1");
        $stmt->bindParam("idcuenta", $_SESSION["user"]["CUENV_IDCUENTA"]);
        //$stmt->bindParam("id", $args['id']);        
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson($data);
    }

    public function fetchSellers(Request $request, Response $response, $args)
    {
        $sSelect = "SELECT `VENDV_CODIEMPR`, `VENDV_IDVENDEDOR`, `VENDV_NOMBRE` FROM `tvendedores`";
        if($_GET['cliente'] != null){
          $sSelect .=  " INNER JOIN tplanrutas ON PLANV_CODIEMPR = VENDV_CODIEMPR AND PLANV_IDVENDEDOR = VENDV_IDVENDEDOR AND PLANV_IDCLIENTE = :cliente";
        }
        $stmt = $this->db->prepare($sSelect);
        if($_GET['cliente'] != null){
          $stmt->bindParam("cliente", $_GET['cliente']);   
        }
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    }

    public function fetchCompany(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `codiempr`, `nombempr` FROM `tempresa` WHERE `codiempr` = :id LIMIT 1");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson($data);
    }

    public function fetchCompanies(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `codiempr`, `nombempr` FROM `tempresa` WHERE 1");
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    }

    public function fetchProduct(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `ARTV_CODIEMPR`, `ARTV_DESCART`, `ARTV_DESCARTDETA`, `ARTV_IDAGRUPAA`,
          `ARTV_IDAGRUPAB`, `ARTV_IDAGRUPAC`, `ARTV_IDTIPO`, `ARTV_IDPRESEN`, `ARTV_IDMARCA`, `ARTV_IDDEPAR`, `ARTV_IDENVASE`,
          `ARTV_IDCATEGORIA`, `ARTN_UNIXCAJA`, `ARTN_CONTXUNI`, `ARTN_COSTOANTE`, `ARTN_COSTOACTU`, `ARTN_COSTOPROM`, `ARTN_PORCIVA`,
          `ARTN_LISAEA`, `ARTV_GRADALCO`, `ARTN_NACIMPOR`, `ARTV_DESCUNIMIN`, `ARTV_DESCUNICOM`, `ARTN_VOLUMEN`, `ARTN_PESO`,
          `ARTN_SADA`, `ARTV_DESCARTCORTA`, `ARTV_REGISSANI`, `ARTV_CPE`, `ARTV_ESPECIE`, `ARTV_PROCEDENCIA`, `ARTN_PORKILOS`,
          `ARTN_STATUS`, `ARTN_CONBALANZA`, `ARTN_PORCUTIL`, `ARTN_PRECIOCAJ`, `ARTN_PRECIOUNI`, `ARTN_PREIVACAJ`, `ARTN_PREIVAUND`,
          `ARTN_PORCUTILM`, `ARTN_RUTAFOTO`, `ARTV_IDDEPOVENT` FROM `tarticulos` WHERE `ARTV_IDARTICULO` = :id order by ARTV_DESCART");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson($data);
    }
    
    public function fetchProducts(Request $request, Response $response, $args)
    {
        //Se buscan las promociones
        $sSelect = "SELECT * FROM ( 
                    SELECT pSys.PROV_CODIEMPR,pSys.PROV_IDPROMO,pSys.PRON_DESDE1,pSys.PRON_DESDE2,pSys.PRON_DESDE3,pSys.PRON_DESDE4,
                      pSys.PRON_DESDE5,pSys.PRON_DESDE6,pSys.PRON_DESDE7,pSys.PRON_DESC1,pSys.PRON_DESC2,
                      pSys.PRON_DESC3,pSys.PRON_DESC4,pSys.PRON_DESC5,pSys.PRON_DESC6,pSys.PRON_DESC7,
                      pSys.PROV_IDARTICULO,pSys.PROV_IDAGRUPAA,pSys.PROV_IDAGRUPAB,pSys.PROV_IDAGRUPAC,pSys.PROV_IDTIPO,pSys.PROV_IDPRESENTACION,
                      pTipo.PRTV_IDTIPOCLIE,pClie.PRCV_IDCLIENTE,
                      IF(pSys.PRON_TIPOEXCL>0,pSys.PRON_TIPOEXCL,IF(pSys.PRON_CLIEXCL>0,pSys.PRON_CLIEXCL,0)) as INCLUIR,
                      IF(pSys.PRON_TIPOEXCL>0,'T',IF(pSys.PRON_CLIEXCL>0,'C','')) AS ORIGEN
                    FROM tpromosis pSys 
                    LEFT JOIN tpromosis_tipos pTipo ON 
                      pSys.PROV_CODIEMPR = pTipo.PRTV_CODIEMPR 
                      AND pSys.PROV_IDPROMO = pTipo.PRTV_IDPROMO 
                      AND PRTV_IDTIPOCLIE = :idtipo1
                    LEFT JOIN tpromosis_clientes pClie ON 
                      pSys.PROV_CODIEMPR = pClie.PRCV_CODIEMPR 
                      AND pSys.PROV_IDPROMO = pClie.PRCV_IDPROMO 
                      AND PRCV_IDCLIENTE = :idcliente1
                    WHERE pSys.PROD_FECHAINI<=NOW() AND pSys.PROD_FECHAFIN >=NOW()
                  ) TpromSis
                  WHERE PROV_CODIEMPR = :idempresa AND
                  IF(ORIGEN='',TRUE,
                    IF(ORIGEN='T',
                    IF(INCLUIR = 2 AND PRTV_IDTIPOCLIE = :idtipo2, FALSE, IF(INCLUIR = 1 AND (PRTV_IDTIPOCLIE != :idtipo3 OR ISNULL(PRTV_IDTIPOCLIE)), FALSE, TRUE)),
                    IF(INCLUIR = 2 AND PRCV_IDCLIENTE = :idcliente2, FALSE, IF(INCLUIR = 1 AND (PRCV_IDCLIENTE != :idcliente3 OR ISNULL(PRCV_IDCLIENTE)), FALSE, TRUE))))";
        $stmt = $this->db->prepare($sSelect);
        $stmt->bindParam("idtipo1", $_GET['idtipo']);        
        $stmt->bindParam("idtipo2", $_GET['idtipo']);        
        $stmt->bindParam("idtipo3", $_GET['idtipo']);        
        $stmt->bindParam("idcliente1", $_GET['idcliente']);
        $stmt->bindParam("idcliente2", $_GET['idcliente']);
        $stmt->bindParam("idcliente3", $_GET['idcliente']);
        $stmt->bindParam("idempresa", $_GET['company']);
        $stmt->execute();      
        $promo = $stmt->fetchAll(); 

        //Se arma el filtro de aplicables
        $sFiltro = "";
        foreach ($promo as $row) {
          $lFiltro = "";
          $lParte1 = "CONCAT(";
          $lParte2 = "CONCAT(";
          $lParte3 = "'";
          $lParte1 .= (empty($row['PROV_IDARTICULO']) ? "" : "a.ARTV_IDARTICULO,");
          $lParte2 .= (empty($row['PROV_IDARTICULO']) ? "" : "tPro.PROV_IDARTICULO,");
          $lParte3 .= (empty($row['PROV_IDARTICULO']) ? "" : ($row['PROV_IDARTICULO']));
          $lParte1 .= (empty($row['PROV_IDAGRUPAA']) ? "" : "a.ARTV_IDAGRUPAA,");
          $lParte2 .= (empty($row['PROV_IDAGRUPAA']) ? "" : "tPro.PROV_IDAGRUPAA,");
          $lParte3 .= (empty($row['PROV_IDAGRUPAA']) ? "" : ($row['PROV_IDAGRUPAA']));
          $lParte1 .= (empty($row['PROV_IDAGRUPAB']) ? "" : "a.ARTV_IDAGRUPAB,");
          $lParte2 .= (empty($row['PROV_IDAGRUPAB']) ? "" : "tPro.PROV_IDAGRUPAB,");
          $lParte3 .= (empty($row['PROV_IDAGRUPAB']) ? "" : ($row['PROV_IDAGRUPAB']));
          $lParte1 .= (empty($row['PROV_IDAGRUPAC']) ? "" : "a.ARTV_IDAGRUPAC,");
          $lParte2 .= (empty($row['PROV_IDAGRUPAC']) ? "" : "tPro.PROV_IDAGRUPAC,");
          $lParte3 .= (empty($row['PROV_IDAGRUPAC']) ? "" : ($row['PROV_IDAGRUPAC']));
          $lParte1 .= (empty($row['PROV_IDTIPO']) ? "" : "a.ARTV_IDTIPO,");
          $lParte2 .= (empty($row['PROV_IDTIPO']) ? "" : "tPro.PROV_IDTIPO,");
          $lParte3 .= (empty($row['PROV_IDTIPO']) ? "" : ($row['PROV_IDTIPO']));
          $lParte1 .= (empty($row['PROV_IDPRESENTACION']) ? "" : "a.ARTV_IDPRESEN,");
          $lParte2 .= (empty($row['PROV_IDPRESENTACION']) ? "" : "tPro.PROV_IDPRESENTACION,");
          $lParte3 .= (empty($row['PROV_IDPRESENTACION']) ? "" : ($row['PROV_IDPRESENTACION']));
          $lParte1 =  substr($lParte1, 0, -1).")";
          $lParte2 =  substr($lParte2, 0, -1).")";
          $lParte3 .=  "'";
          $lFiltro = "(" . $lParte1 . " = "  . $lParte2 . " AND " . $lParte2 . " = " . $lParte3 . ") OR";
          $sFiltro .=  $lFiltro;
        }
        if(!empty($sFiltro)){
          $sFiltro = substr($sFiltro, 0, -3);
          $sSelect = "SELECT a.ARTV_CODIEMPR,a.ARTV_IDARTICULO,a.ARTV_IDAGRUPAA,a.ARTV_IDAGRUPAB,a.ARTV_IDAGRUPAC,a.ARTV_IDTIPO,a.ARTV_IDPRESEN,  
            a.ARTV_DESCART,a.ARTN_PORCIVA,a.ARTV_DESCUNIMIN,a.ARTN_UNIXCAJA,tagrupaa.GRUCV_NOMBRE,tagrupab.SUBGPV_NOMBRE,
            tagrupac.TLINV_NOMBRE,tPre.PREDV_TIPO,tPre.PREDN_PRECIOCAJ AS ARTN_PRECIOCAJ,tPre.PREDN_PREIVACAJ AS ARTN_PREIVACAJ,
            IFNULL(tPro.PROV_IDPROMO,'') as PROV_IDPROMO,IFNULL(tPro.PRON_DESDE1,0.00) as PRON_DESDE1,IFNULL(tPro.PRON_DESC1,0.00) as PRON_DESC1,
            a.ARTN_UNDMIN,
            texisdepo.EXDEV_UNIDADES,
            texisdepo.EXDEN_UNIXCAJA,
            texisdepo.EXDEN_UNIXCAJA/texisdepo.EXDEV_UNIDADES as CAJAS

          FROM tarticulos a
          INNER JOIN tprecios tPre ON a.ARTV_CODIEMPR = tPre.PREDV_CODIEMPR AND a.ARTV_IDARTICULO = tPre.PREDV_IDARTICULO
          INNER JOIN tagrupaa ON a.ARTV_IDAGRUPAA = tagrupaa.GRUCV_IDAGRUPAA AND a.ARTV_CODIEMPR = tagrupaa.GRUCV_CODIEMPR
          INNER JOIN tagrupab ON a.ARTV_IDAGRUPAA = tagrupab.SUBGPV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupab.SUBGPV_IDAGRUPAB AND a.ARTV_CODIEMPR = tagrupab.SUBGPV_CODIEMPR
          INNER JOIN tagrupac ON a.ARTV_IDAGRUPAA = tagrupac.TLINV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupac.TLINV_IDAGRUPAB AND a.ARTV_IDAGRUPAC = tagrupac.TLINV_IDAGRUPAC AND a.ARTV_CODIEMPR = tagrupac.TLINV_CODIEMPR 
          INNER JOIN texisdepo ON a.ARTV_CODIEMPR = texisdepo.EXDEV_CODIEMPR AND a.ARTV_IDARTICULO = texisdepo.EXDEV_IDARTICULO
          JOIN tcuentas_ven c ON a.ARTV_CODIEMPR = c.CRVV_CODIEMPR 
          LEFT JOIN (
            SELECT * FROM ( 
                    SELECT pSys.PROV_CODIEMPR,pSys.PROV_IDPROMO,pSys.PRON_DESDE1,pSys.PRON_DESDE2,pSys.PRON_DESDE3,pSys.PRON_DESDE4,
                      pSys.PRON_DESDE5,pSys.PRON_DESDE6,pSys.PRON_DESDE7,pSys.PRON_DESC1,pSys.PRON_DESC2,
                      pSys.PRON_DESC3,pSys.PRON_DESC4,pSys.PRON_DESC5,pSys.PRON_DESC6,pSys.PRON_DESC7,
                      pSys.PROV_IDARTICULO,pSys.PROV_IDAGRUPAA,pSys.PROV_IDAGRUPAB,pSys.PROV_IDAGRUPAC,pSys.PROV_IDTIPO,pSys.PROV_IDPRESENTACION,
                      pTipo.PRTV_IDTIPOCLIE,pClie.PRCV_IDCLIENTE,
                      IF(pSys.PRON_TIPOEXCL>0,pSys.PRON_TIPOEXCL,IF(pSys.PRON_CLIEXCL>0,pSys.PRON_CLIEXCL,0)) as INCLUIR,
                      IF(pSys.PRON_TIPOEXCL>0,'T',IF(pSys.PRON_CLIEXCL>0,'C','')) AS ORIGEN
                    FROM tpromosis pSys 
                    LEFT JOIN tpromosis_tipos pTipo ON 
                      pSys.PROV_CODIEMPR = pTipo.PRTV_CODIEMPR 
                      AND pSys.PROV_IDPROMO = pTipo.PRTV_IDPROMO 
                      AND PRTV_IDTIPOCLIE = :idtipo1
                    LEFT JOIN tpromosis_clientes pClie ON 
                      pSys.PROV_CODIEMPR = pClie.PRCV_CODIEMPR 
                      AND pSys.PROV_IDPROMO = pClie.PRCV_IDPROMO 
                      AND PRCV_IDCLIENTE = :idcliente1
                    WHERE pSys.PROD_FECHAINI<=NOW() AND pSys.PROD_FECHAFIN >=NOW()
                  ) TpromSis
                  WHERE PROV_CODIEMPR = :idempresa AND
                  IF(ORIGEN='',TRUE,
                    IF(ORIGEN='T',
                    IF(INCLUIR = 2 AND PRTV_IDTIPOCLIE = :idtipo2, FALSE, IF(INCLUIR = 1 AND (PRTV_IDTIPOCLIE != :idtipo3 OR ISNULL(PRTV_IDTIPOCLIE)), FALSE, TRUE)),
                    IF(INCLUIR = 2 AND PRCV_IDCLIENTE = :idcliente2, FALSE, IF(INCLUIR = 1 AND (PRCV_IDCLIENTE != :idcliente3 OR ISNULL(PRCV_IDCLIENTE)), FALSE, TRUE))))
          ) tPro ON tPro.PROV_CODIEMPR = a.ARTV_CODIEMPR AND (".$sFiltro.") WHERE c.CRVN_IDCUENTA = :id AND tPre.PREDV_TIPO = :tipoprec AND texisdepo.EXDEV_UNIDADES >= a.ARTN_UNDMIN AND texisdepo.EXDEV_UNIDADES > 0";
          if($_GET['gr'] != null){
            $sSelect .= ($_GET['gr']!="all" ? " AND tagrupaa.GRUCV_NOMBRE = '".$_GET['gr']."'" : "");
          }
          if($_GET['sg'] != null){
            $sSelect .= ($_GET['sg']!="all" ? " AND tagrupab.SUBGPV_NOMBRE = '".$_GET['sg']."'" : "");
          }
          if($_GET['li'] != null){
            $sSelect .= ($_GET['li']!="all" ? " AND tagrupac.TLINV_NOMBRE = '".$_GET['li']."'" : "");
          }
          if($_GET['pr'] != null){
            $sSelect .= ($_GET['pr']!="" ? " AND CONCAT(a.ARTV_IDARTICULO,a.ARTV_DESCART) LIKE '%".$_GET['pr']."%'" : "");
          }
          $sSelect .= " GROUP BY a.ARTV_CODIEMPR,a.ARTV_IDARTICULO,tPre.PREDV_TIPO   ORDER BY CAJAS ASC";
          if($_GET['in'] != null){
            $sSelect .= " LIMIT ".$_GET['in'].",20";
          }          

          $stmt = $this->db->prepare($sSelect);
          $stmt->bindParam("idtipo1", $_GET['idtipo']);        
          $stmt->bindParam("idtipo2", $_GET['idtipo']);        
          $stmt->bindParam("idtipo3", $_GET['idtipo']);        
          $stmt->bindParam("idcliente1", $_GET['idcliente']);
          $stmt->bindParam("idcliente2", $_GET['idcliente']);
          $stmt->bindParam("idcliente3", $_GET['idcliente']);
          $stmt->bindParam("idempresa", $_GET['company']);
          $stmt->bindParam("id", $_SESSION["user"]["CUENV_IDCUENTA"]);
          $stmt->bindParam("tipoprec", $_GET['tipoprec']);
          $stmt->execute();      
          $data = $stmt->fetchAll();
        }else{
          $sSelect = "SELECT a.ARTV_CODIEMPR,a.ARTV_DESCUNIMIN,a.ARTV_IDARTICULO,a.ARTV_DESCART,a.ARTN_PORCIVA,a.ARTN_UNIXCAJA,tagrupaa.GRUCV_NOMBRE,tagrupab.SUBGPV_NOMBRE,
          tagrupac.TLINV_NOMBRE,tprecios.PREDV_TIPO,tprecios.PREDN_PRECIOCAJ AS ARTN_PRECIOCAJ,tprecios.PREDN_PREIVACAJ AS ARTN_PREIVACAJ,
            '' as PROV_IDPROMO,0 as PRON_DESDE1,0 as PRON_DESDE2,0 as PRON_DESDE3,0 as PRON_DESDE4,
            0 as PRON_DESDE5,0 as PRON_DESDE6,0 as PRON_DESDE7,0.00 as PRON_DESC1,0.00 as PRON_DESC2,
            0.00 as PRON_DESC3,0.00 as PRON_DESC4,0.00 as PRON_DESC5,0.00 as PRON_DESC6,0.00 as PRON_DESC7,
            a.ARTN_UNDMIN,
            texisdepo.EXDEV_UNIDADES,
            texisdepo.EXDEN_UNIXCAJA,
            texisdepo.EXDEN_UNIXCAJA/texisdepo.EXDEV_UNIDADES as CAJAS
          FROM tarticulos a
          INNER JOIN tprecios ON a.ARTV_CODIEMPR = tprecios.PREDV_CODIEMPR AND a.ARTV_IDARTICULO = tprecios.PREDV_IDARTICULO
          INNER JOIN tagrupaa ON a.ARTV_IDAGRUPAA = tagrupaa.GRUCV_IDAGRUPAA AND a.ARTV_CODIEMPR = tagrupaa.GRUCV_CODIEMPR
          INNER JOIN tagrupab ON a.ARTV_IDAGRUPAA = tagrupab.SUBGPV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupab.SUBGPV_IDAGRUPAB AND a.ARTV_CODIEMPR = tagrupab.SUBGPV_CODIEMPR
          INNER JOIN tagrupac ON a.ARTV_IDAGRUPAA = tagrupac.TLINV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupac.TLINV_IDAGRUPAB AND a.ARTV_IDAGRUPAC = tagrupac.TLINV_IDAGRUPAC AND a.ARTV_CODIEMPR = tagrupac.TLINV_CODIEMPR 
          INNER JOIN texisdepo ON a.ARTV_CODIEMPR = texisdepo.EXDEV_CODIEMPR AND a.ARTV_IDARTICULO = texisdepo.EXDEV_IDARTICULO
          JOIN tcuentas_ven c ON a.ARTV_CODIEMPR = c.CRVV_CODIEMPR 
          WHERE c.CRVN_IDCUENTA = :id AND tprecios.PREDV_TIPO = :tipoprec AND texisdepo.EXDEV_UNIDADES>=a.ARTN_UNDMIN AND texisdepo.EXDEV_UNIDADES > 0 AND tprecios.PREDN_PRECIOCAJ > 0";
          if($_GET['gr'] != null){
            $sSelect .= ($_GET['gr']!="all" ? " AND tagrupaa.GRUCV_NOMBRE = '".$_GET['gr']."'" : "");
          }
          if($_GET['sg'] != null){
            $sSelect .= ($_GET['sg']!="all" ? " AND tagrupab.SUBGPV_NOMBRE = '".$_GET['sg']."'" : "");
          }
          if($_GET['li'] != null){
            $sSelect .= ($_GET['li']!="all" ? " AND tagrupac.TLINV_NOMBRE = '".$_GET['li']."'" : "");
          }
          if($_GET['pr'] != null){
            $sSelect .= ($_GET['pr']!="" ? " AND CONCAT(a.ARTV_IDARTICULO,a.ARTV_DESCART) LIKE '%".$_GET['pr']."%'" : "");
          }
          $sSelect .= " GROUP BY a.ARTV_CODIEMPR,a.ARTV_IDARTICULO,tprecios.PREDV_TIPO ORDER BY CAJAS";
          if($_GET['in'] != null){
            $sSelect .= " LIMIT ".$_GET['in'].",20";
          }
          $stmt = $this->db->prepare($sSelect);
          $stmt->bindParam("id", $_SESSION["user"]["CUENV_IDCUENTA"]);
          $stmt->bindParam("tipoprec", $_GET['tipoprec']);    
          $stmt->execute();      
          $data = $stmt->fetchAll();
        }        
        if($_GET['in'] != null){          
          return $response->withJson($data);
        }else{          
          $nRows = count($data);
          return $response->withJson(array(
            "id" => $nRows
          ));        
        }
    }
    
    public function fetchClientProducts(Request $request, Response $response, $args)
    {
        $NOT_IN='';
        $DESACTIVE='';
        if($_SESSION["user"]["CUENV_TIPO"] == 2){
            $NOT_INT='';
            $DESACTIVE=" AND tagrupaa.GRUCV_STATUS = '1' ";
            $NOT=array();
            
            $d=array();
            $stmt = $this->db->prepare("SELECT	limit_prov.codeprov FROM limit_prov where codeclie = :idcliente");
            $stmt->bindParam('idcliente', $_SESSION['user']['details']['CRCV_IDCLIENTE']);
            $stmt->execute();
            $d = $stmt->fetchAll();
            
            $n=" AND tagrupaa.GRUCV_IDAGRUPAA NOT IN (";
            foreach($d as $r){
              $n.="'".$r['codeprov']."',";
            }
            $n = substr($n, 0, -1);
            $n.=") ";
            if(!empty($d))
                $NOT_IN=$n;
            
        }
        
        //Se buscan las promociones
        $sSelect = "SELECT * FROM (
              SELECT pWeb.PROV_CODIEMPR,pWeb.PROV_IDPROMO,pWeb.PRON_DESDE1,pWeb.PRON_DESDE2,pWeb.PRON_DESDE3,pWeb.PRON_DESDE4,
                pWeb.PRON_DESDE5,pWeb.PRON_DESDE6,pWeb.PRON_DESDE7,pWeb.PRON_DESC1,pWeb.PRON_DESC2,
                pWeb.PRON_DESC3,pWeb.PRON_DESC4,pWeb.PRON_DESC5,pWeb.PRON_DESC6,pWeb.PRON_DESC7,
                pWeb.PROV_IDARTICULO,pWeb.PROV_IDAGRUPAA,pWeb.PROV_IDAGRUPAB,pWeb.PROV_IDAGRUPAC,pWeb.PROV_IDTIPO,pWeb.PROV_IDPRESENTACION,
                pTipo.PRTV_IDTIPOCLIE,pClie.PRCV_IDCLIENTE,
                IF(pWeb.PRON_TIPOEXCL>0,pWeb.PRON_TIPOEXCL,IF(pWeb.PRON_CLIEXCL>0,pWeb.PRON_CLIEXCL,0)) as INCLUIR,
                IF(pWeb.PRON_TIPOEXCL>0,'T',IF(pWeb.PRON_CLIEXCL>0,'C','')) AS ORIGEN
              FROM tpromoweb pWeb 
              LEFT JOIN tpromoweb_tipos pTipo ON 
                pWeb.PROV_CODIEMPR = pTipo.PRTV_CODIEMPR 
                AND pWeb.PROV_IDPROMO = pTipo.PRTV_IDPROMO 
                AND PRTV_IDTIPOCLIE = :idtipo1 
              LEFT JOIN tpromoweb_clientes pClie ON 
                pWeb.PROV_CODIEMPR = pClie.PRCV_CODIEMPR 
                AND pWeb.PROV_IDPROMO = pClie.PRCV_IDPROMO 
                AND PRCV_IDCLIENTE = :idcliente1 
              WHERE pWeb.PROD_FECHAINI<=NOW() AND pWeb.PROD_FECHAFIN >=NOW()
            ) tpromoWeb
            WHERE PROV_CODIEMPR = :idempresa AND
            IF(ORIGEN='',TRUE,
            IF(ORIGEN='T',
              IF(INCLUIR = 2 AND PRTV_IDTIPOCLIE = :idtipo2, FALSE, IF(INCLUIR = 1 AND (PRTV_IDTIPOCLIE != :idtipo3 OR ISNULL(PRTV_IDTIPOCLIE)), FALSE, TRUE)),
              IF(INCLUIR = 2 AND PRCV_IDCLIENTE = :idcliente2, FALSE, IF(INCLUIR = 1 AND (PRCV_IDCLIENTE != :idcliente3 OR ISNULL(PRCV_IDCLIENTE)), FALSE, TRUE))))
          UNION
            SELECT * FROM ( 
              SELECT pSys.PROV_CODIEMPR,pSys.PROV_IDPROMO,pSys.PRON_DESDE1,pSys.PRON_DESDE2,pSys.PRON_DESDE3,pSys.PRON_DESDE4,
                pSys.PRON_DESDE5,pSys.PRON_DESDE6,pSys.PRON_DESDE7,pSys.PRON_DESC1,pSys.PRON_DESC2,
                pSys.PRON_DESC3,pSys.PRON_DESC4,pSys.PRON_DESC5,pSys.PRON_DESC6,pSys.PRON_DESC7,
                pSys.PROV_IDARTICULO,pSys.PROV_IDAGRUPAA,pSys.PROV_IDAGRUPAB,pSys.PROV_IDAGRUPAC,pSys.PROV_IDTIPO,pSys.PROV_IDPRESENTACION,
                pTipo.PRTV_IDTIPOCLIE,pClie.PRCV_IDCLIENTE,
                IF(pSys.PRON_TIPOEXCL>0,pSys.PRON_TIPOEXCL,IF(pSys.PRON_CLIEXCL>0,pSys.PRON_CLIEXCL,0)) as INCLUIR,
                IF(pSys.PRON_TIPOEXCL>0,'T',IF(pSys.PRON_CLIEXCL>0,'C','')) AS ORIGEN
              FROM tpromosis pSys 
              LEFT JOIN tpromosis_tipos pTipo ON 
                pSys.PROV_CODIEMPR = pTipo.PRTV_CODIEMPR 
                AND pSys.PROV_IDPROMO = pTipo.PRTV_IDPROMO 
                AND PRTV_IDTIPOCLIE = :idtipo4
              LEFT JOIN tpromosis_clientes pClie ON 
                pSys.PROV_CODIEMPR = pClie.PRCV_CODIEMPR 
                AND pSys.PROV_IDPROMO = pClie.PRCV_IDPROMO 
                AND PRCV_IDCLIENTE = :idcliente4
              WHERE pSys.PROD_FECHAINI<=NOW() AND pSys.PROD_FECHAFIN >=NOW()
            ) TpromSis
            WHERE PROV_CODIEMPR = :idempresa2 AND
            IF(ORIGEN='',TRUE,
            IF(ORIGEN='T',
              IF(INCLUIR = 2 AND PRTV_IDTIPOCLIE = :idtipo5, FALSE, IF(INCLUIR = 1 AND (PRTV_IDTIPOCLIE != :idtipo6 OR ISNULL(PRTV_IDTIPOCLIE)), FALSE, TRUE)),
              IF(INCLUIR = 2 AND PRCV_IDCLIENTE = :idcliente5, FALSE, IF(INCLUIR = 1 AND (PRCV_IDCLIENTE != :idcliente6 OR ISNULL(PRCV_IDCLIENTE)), FALSE, TRUE))))";
        $stmt = $this->db->prepare($sSelect);
        $stmt->bindParam("idtipo1", $_GET['idtipo']);        
        $stmt->bindParam("idtipo2", $_GET['idtipo']);        
        $stmt->bindParam("idtipo3", $_GET['idtipo']);        
        $stmt->bindParam("idtipo4", $_GET['idtipo']);        
        $stmt->bindParam("idtipo5", $_GET['idtipo']);        
        $stmt->bindParam("idtipo6", $_GET['idtipo']);        
        $stmt->bindParam("idcliente1", $_GET['idcliente']);
        $stmt->bindParam("idcliente2", $_GET['idcliente']);
        $stmt->bindParam("idcliente3", $_GET['idcliente']);
        $stmt->bindParam("idcliente4", $_GET['idcliente']);
        $stmt->bindParam("idcliente5", $_GET['idcliente']);
        $stmt->bindParam("idcliente6", $_GET['idcliente']);
        $stmt->bindParam("idempresa", $_GET['company']);
        $stmt->bindParam("idempresa2", $_GET['company']);
        $stmt->execute();      
        $promo = $stmt->fetchAll(); 

        //Se arma el filtro de aplicables
        $sFiltro = "";
        foreach ($promo as $row) {
          $lFiltro = "";
          $lParte1 = "CONCAT(";
          $lParte2 = "CONCAT(";
          $lParte3 = "'";
          $lParte1 .= (empty($row['PROV_IDARTICULO']) ? "" : "a.ARTV_IDARTICULO,");
          $lParte2 .= (empty($row['PROV_IDARTICULO']) ? "" : "tPro.PROV_IDARTICULO,");
          $lParte3 .= (empty($row['PROV_IDARTICULO']) ? "" : ($row['PROV_IDARTICULO']));
          $lParte1 .= (empty($row['PROV_IDAGRUPAA']) ? "" : "a.ARTV_IDAGRUPAA,");
          $lParte2 .= (empty($row['PROV_IDAGRUPAA']) ? "" : "tPro.PROV_IDAGRUPAA,");
          $lParte3 .= (empty($row['PROV_IDAGRUPAA']) ? "" : ($row['PROV_IDAGRUPAA']));
          $lParte1 .= (empty($row['PROV_IDAGRUPAB']) ? "" : "a.ARTV_IDAGRUPAB,");
          $lParte2 .= (empty($row['PROV_IDAGRUPAB']) ? "" : "tPro.PROV_IDAGRUPAB,");
          $lParte3 .= (empty($row['PROV_IDAGRUPAB']) ? "" : ($row['PROV_IDAGRUPAB']));
          $lParte1 .= (empty($row['PROV_IDAGRUPAC']) ? "" : "a.ARTV_IDAGRUPAC,");
          $lParte2 .= (empty($row['PROV_IDAGRUPAC']) ? "" : "tPro.PROV_IDAGRUPAC,");
          $lParte3 .= (empty($row['PROV_IDAGRUPAC']) ? "" : ($row['PROV_IDAGRUPAC']));
          $lParte1 .= (empty($row['PROV_IDTIPO']) ? "" : "a.ARTV_IDTIPO,");
          $lParte2 .= (empty($row['PROV_IDTIPO']) ? "" : "tPro.PROV_IDTIPO,");
          $lParte3 .= (empty($row['PROV_IDTIPO']) ? "" : ($row['PROV_IDTIPO']));
          $lParte1 .= (empty($row['PROV_IDPRESENTACION']) ? "" : "a.ARTV_IDPRESEN,");
          $lParte2 .= (empty($row['PROV_IDPRESENTACION']) ? "" : "tPro.PROV_IDPRESENTACION,");
          $lParte3 .= (empty($row['PROV_IDPRESENTACION']) ? "" : ($row['PROV_IDPRESENTACION']));
          $lParte1 =  substr($lParte1, 0, -1).")";
          $lParte2 =  substr($lParte2, 0, -1).")";
          $lParte3 .=  "'";
          $lFiltro = "(" . $lParte1 . " = "  . $lParte2 . " AND " . $lParte2 . " = " . $lParte3 . ") OR";
          $sFiltro .=  $lFiltro;
        }
        if(!empty($sFiltro)){
          $sFiltro = substr($sFiltro, 0, -3);
          $sSelect = "SELECT a.ARTV_CODIEMPR,a.ARTV_IDARTICULO,a.ARTV_IDAGRUPAA,a.ARTV_IDAGRUPAB,a.ARTV_IDAGRUPAC,a.ARTV_IDTIPO,a.ARTV_IDPRESEN,
            a.ARTV_DESCART,a.ARTN_PORCIVA,a.ARTV_DESCUNIMIN,a.ARTN_UNIXCAJA,tagrupaa.GRUCV_NOMBRE,tagrupab.SUBGPV_NOMBRE,
            tagrupac.TLINV_NOMBRE,tPre.PREDV_TIPO,tPre.PREDN_PRECIOCAJ AS ARTN_PRECIOCAJ,tPre.PREDN_PREIVACAJ AS ARTN_PREIVACAJ,
            IFNULL(tPro.PROV_IDPROMO,'') as PROV_IDPROMO,IFNULL(tPro.PRON_DESDE1,0.00) as PRON_DESDE1,IFNULL(tPro.PRON_DESC1,0.00) as PRON_DESC1,
            a.ARTN_UNDMIN
          FROM tarticulos a
          INNER JOIN tprecios tPre ON a.ARTV_CODIEMPR = tPre.PREDV_CODIEMPR AND a.ARTV_IDARTICULO = tPre.PREDV_IDARTICULO
          INNER JOIN tagrupaa ON a.ARTV_IDAGRUPAA = tagrupaa.GRUCV_IDAGRUPAA AND a.ARTV_CODIEMPR = tagrupaa.GRUCV_CODIEMPR
          INNER JOIN tagrupab ON a.ARTV_IDAGRUPAA = tagrupab.SUBGPV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupab.SUBGPV_IDAGRUPAB AND a.ARTV_CODIEMPR = tagrupab.SUBGPV_CODIEMPR
          INNER JOIN tagrupac ON a.ARTV_IDAGRUPAA = tagrupac.TLINV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupac.TLINV_IDAGRUPAB AND a.ARTV_IDAGRUPAC = tagrupac.TLINV_IDAGRUPAC AND a.ARTV_CODIEMPR = tagrupac.TLINV_CODIEMPR 
          INNER JOIN texisdepo ON a.ARTV_CODIEMPR = texisdepo.EXDEV_CODIEMPR AND a.ARTV_IDARTICULO = texisdepo.EXDEV_IDARTICULO
          INNER JOIN tcuentas_cli c ON a.ARTV_CODIEMPR = c.CRCV_CODIEMPR
          LEFT JOIN (
            SELECT * FROM (
              SELECT pWeb.PROV_CODIEMPR,pWeb.PROV_IDPROMO,pWeb.PRON_DESDE1,pWeb.PRON_DESDE2,pWeb.PRON_DESDE3,pWeb.PRON_DESDE4,
                pWeb.PRON_DESDE5,pWeb.PRON_DESDE6,pWeb.PRON_DESDE7,pWeb.PRON_DESC1,pWeb.PRON_DESC2,
                pWeb.PRON_DESC3,pWeb.PRON_DESC4,pWeb.PRON_DESC5,pWeb.PRON_DESC6,pWeb.PRON_DESC7,
                pWeb.PROV_IDARTICULO,pWeb.PROV_IDAGRUPAA,pWeb.PROV_IDAGRUPAB,pWeb.PROV_IDAGRUPAC,pWeb.PROV_IDTIPO,pWeb.PROV_IDPRESENTACION,
                pTipo.PRTV_IDTIPOCLIE,pClie.PRCV_IDCLIENTE,
                IF(pWeb.PRON_TIPOEXCL>0,pWeb.PRON_TIPOEXCL,IF(pWeb.PRON_CLIEXCL>0,pWeb.PRON_CLIEXCL,0)) as INCLUIR,
                IF(pWeb.PRON_TIPOEXCL>0,'T',IF(pWeb.PRON_CLIEXCL>0,'C','')) AS ORIGEN
              FROM tpromoweb pWeb 
              LEFT JOIN tpromoweb_tipos pTipo ON 
                pWeb.PROV_CODIEMPR = pTipo.PRTV_CODIEMPR 
                AND pWeb.PROV_IDPROMO = pTipo.PRTV_IDPROMO 
                AND PRTV_IDTIPOCLIE = :idtipo1 
              LEFT JOIN tpromoweb_clientes pClie ON 
                pWeb.PROV_CODIEMPR = pClie.PRCV_CODIEMPR 
                AND pWeb.PROV_IDPROMO = pClie.PRCV_IDPROMO 
                AND PRCV_IDCLIENTE = :idcliente1 
              WHERE pWeb.PROD_FECHAINI<=NOW() AND pWeb.PROD_FECHAFIN >=NOW()
            ) tpromoWeb
            WHERE PROV_CODIEMPR = :idempresa AND
            IF(ORIGEN='',TRUE,
            IF(ORIGEN='T',
              IF(INCLUIR = 2 AND PRTV_IDTIPOCLIE = :idtipo2, FALSE, IF(INCLUIR = 1 AND (PRTV_IDTIPOCLIE != :idtipo3 OR ISNULL(PRTV_IDTIPOCLIE)), FALSE, TRUE)),
              IF(INCLUIR = 2 AND PRCV_IDCLIENTE = :idcliente2, FALSE, IF(INCLUIR = 1 AND (PRCV_IDCLIENTE != :idcliente3 OR ISNULL(PRCV_IDCLIENTE)), FALSE, TRUE))))
          UNION
            SELECT * FROM ( 
              SELECT pSys.PROV_CODIEMPR,pSys.PROV_IDPROMO,pSys.PRON_DESDE1,pSys.PRON_DESDE2,pSys.PRON_DESDE3,pSys.PRON_DESDE4,
                pSys.PRON_DESDE5,pSys.PRON_DESDE6,pSys.PRON_DESDE7,pSys.PRON_DESC1,pSys.PRON_DESC2,
                pSys.PRON_DESC3,pSys.PRON_DESC4,pSys.PRON_DESC5,pSys.PRON_DESC6,pSys.PRON_DESC7,
                pSys.PROV_IDARTICULO,pSys.PROV_IDAGRUPAA,pSys.PROV_IDAGRUPAB,pSys.PROV_IDAGRUPAC,pSys.PROV_IDTIPO,pSys.PROV_IDPRESENTACION,
                pTipo.PRTV_IDTIPOCLIE,pClie.PRCV_IDCLIENTE,
                IF(pSys.PRON_TIPOEXCL>0,pSys.PRON_TIPOEXCL,IF(pSys.PRON_CLIEXCL>0,pSys.PRON_CLIEXCL,0)) as INCLUIR,
                IF(pSys.PRON_TIPOEXCL>0,'T',IF(pSys.PRON_CLIEXCL>0,'C','')) AS ORIGEN
              FROM tpromosis pSys 
              LEFT JOIN tpromosis_tipos pTipo ON 
                pSys.PROV_CODIEMPR = pTipo.PRTV_CODIEMPR 
                AND pSys.PROV_IDPROMO = pTipo.PRTV_IDPROMO 
                AND PRTV_IDTIPOCLIE = :idtipo4
              LEFT JOIN tpromosis_clientes pClie ON 
                pSys.PROV_CODIEMPR = pClie.PRCV_CODIEMPR 
                AND pSys.PROV_IDPROMO = pClie.PRCV_IDPROMO 
                AND PRCV_IDCLIENTE = :idcliente4
              WHERE pSys.PROD_FECHAINI<=NOW() AND pSys.PROD_FECHAFIN >=NOW()
            ) TpromSis
            WHERE PROV_CODIEMPR = :idempresa2 AND
            IF(ORIGEN='',TRUE,
            IF(ORIGEN='T',
              IF(INCLUIR = 2 AND PRTV_IDTIPOCLIE = :idtipo5, FALSE, IF(INCLUIR = 1 AND (PRTV_IDTIPOCLIE != :idtipo6 OR ISNULL(PRTV_IDTIPOCLIE)), FALSE, TRUE)),
              IF(INCLUIR = 2 AND PRCV_IDCLIENTE = :idcliente5, FALSE, IF(INCLUIR = 1 AND (PRCV_IDCLIENTE != :idcliente6 OR ISNULL(PRCV_IDCLIENTE)), FALSE, TRUE))))
          ) tPro ON tPro.PROV_CODIEMPR = a.ARTV_CODIEMPR AND (".$sFiltro.") WHERE c.CRCN_IDCUENTA = :id AND tPre.PREDV_TIPO = :tipoprec AND texisdepo.EXDEV_UNIDADES>=a.ARTN_UNDMIN";
          if($_GET['gr'] != null){
            $sSelect .= ($_GET['gr']!="all" ? " AND tagrupaa.GRUCV_NOMBRE = '".$_GET['gr']."'" : "");
          }
          if($_GET['sg'] != null){
            $sSelect .= ($_GET['sg']!="all" ? " AND tagrupab.SUBGPV_NOMBRE = '".$_GET['sg']."'" : "");
          }
          if($_GET['li'] != null){
            $sSelect .= ($_GET['li']!="all" ? " AND tagrupac.TLINV_NOMBRE = '".$_GET['li']."'" : "");
          }
          if($_GET['pr'] != null){
            $sSelect .= ($_GET['pr']!="" ? " AND CONCAT(a.ARTV_IDARTICULO,a.ARTV_DESCART) LIKE '%".$_GET['pr']."%'" : "");
          }
          $sSelect .= " GROUP BY a.ARTV_CODIEMPR,a.ARTV_IDARTICULO,tPre.PREDV_TIPO";
          if($_GET['in'] != null){
            $sSelect .= " LIMIT ".$_GET['in'].",20";
          }          

          $stmt = $this->db->prepare($sSelect);
          $stmt->bindParam("idtipo1", $_GET['idtipo']);        
          $stmt->bindParam("idtipo2", $_GET['idtipo']);        
          $stmt->bindParam("idtipo3", $_GET['idtipo']);        
          $stmt->bindParam("idtipo4", $_GET['idtipo']);        
          $stmt->bindParam("idtipo5", $_GET['idtipo']);        
          $stmt->bindParam("idtipo6", $_GET['idtipo']);        
          $stmt->bindParam("idcliente1", $_GET['idcliente']);
          $stmt->bindParam("idcliente2", $_GET['idcliente']);
          $stmt->bindParam("idcliente3", $_GET['idcliente']);
          $stmt->bindParam("idcliente4", $_GET['idcliente']);
          $stmt->bindParam("idcliente5", $_GET['idcliente']);
          $stmt->bindParam("idcliente6", $_GET['idcliente']);
          $stmt->bindParam("idempresa", $_GET['company']);
          $stmt->bindParam("idempresa2", $_GET['company']);
          $stmt->bindParam("id", $_SESSION["user"]["CUENV_IDCUENTA"]);
          $stmt->bindParam("tipoprec", $_GET['tipoprec']);
          $stmt->execute();      
          $data = $stmt->fetchAll();
        }else{
          $sSelect = "SELECT a.ARTV_CODIEMPR,a.ARTV_IDARTICULO,a.ARTV_DESCART,a.ARTN_PORCIVA,a.ARTN_UNIXCAJA,tagrupaa.GRUCV_NOMBRE,tagrupab.SUBGPV_NOMBRE,
          tagrupac.TLINV_NOMBRE,a.ARTV_DESCUNIMIN,tprecios.PREDV_TIPO,tprecios.PREDN_PRECIOCAJ AS ARTN_PRECIOCAJ,tprecios.PREDN_PREIVACAJ AS ARTN_PREIVACAJ,
            '' as PROV_IDPROMO,0 as PRON_DESDE1,0 as PRON_DESDE2,0 as PRON_DESDE3,0 as PRON_DESDE4,
            0 as PRON_DESDE5,0 as PRON_DESDE6,0 as PRON_DESDE7,0.00 as PRON_DESC1,0.00 as PRON_DESC2,
            0.00 as PRON_DESC3,0.00 as PRON_DESC4,0.00 as PRON_DESC5,0.00 as PRON_DESC6,0.00 as PRON_DESC7,
            a.ARTN_UNDMIN
          FROM tarticulos a
          INNER JOIN tprecios ON a.ARTV_CODIEMPR = tprecios.PREDV_CODIEMPR AND a.ARTV_IDARTICULO = tprecios.PREDV_IDARTICULO
          INNER JOIN tagrupaa ON a.ARTV_IDAGRUPAA = tagrupaa.GRUCV_IDAGRUPAA AND a.ARTV_CODIEMPR = tagrupaa.GRUCV_CODIEMPR
          INNER JOIN tagrupab ON a.ARTV_IDAGRUPAA = tagrupab.SUBGPV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupab.SUBGPV_IDAGRUPAB AND a.ARTV_CODIEMPR = tagrupab.SUBGPV_CODIEMPR
          INNER JOIN tagrupac ON a.ARTV_IDAGRUPAA = tagrupac.TLINV_IDAGRUPAA AND a.ARTV_IDAGRUPAB = tagrupac.TLINV_IDAGRUPAB AND a.ARTV_IDAGRUPAC = tagrupac.TLINV_IDAGRUPAC AND a.ARTV_CODIEMPR = tagrupac.TLINV_CODIEMPR
          INNER JOIN tcuentas_cli c ON a.ARTV_CODIEMPR = c.CRCV_CODIEMPR 
          INNER JOIN texisdepo ON a.ARTV_CODIEMPR = texisdepo.EXDEV_CODIEMPR AND a.ARTV_IDARTICULO = texisdepo.EXDEV_IDARTICULO
          WHERE c.CRCN_IDCUENTA = :id AND tprecios.PREDV_TIPO = :tipoprec AND texisdepo.EXDEV_UNIDADES>=a.ARTN_UNDMIN AND ARTN_PRECIOCAJ > 0";
          $sSelect .= $DESACTIVE;
          $sSelect .= $NOT_IN;
          if($_GET['gr'] != null){
            $sSelect .= ($_GET['gr']!="all" ? " AND tagrupaa.GRUCV_NOMBRE = '".$_GET['gr']."'" : "");
          }
          if($_GET['sg'] != null){
            $sSelect .= ($_GET['sg']!="all" ? " AND tagrupab.SUBGPV_NOMBRE = '".$_GET['sg']."'" : "");
          }
          if($_GET['li'] != null){
            $sSelect .= ($_GET['li']!="all" ? " AND tagrupac.TLINV_NOMBRE = '".$_GET['li']."'" : "");
          }
          if($_GET['pr'] != null){
            $sSelect .= ($_GET['pr']!="" ? " AND CONCAT(a.ARTV_IDARTICULO,a.ARTV_DESCART) LIKE '%".$_GET['pr']."%'" : "");
          }
          
          $sSelect .= " GROUP BY a.ARTV_CODIEMPR,a.ARTV_IDARTICULO,tprecios.PREDV_TIPO";
          if($_GET['in'] != null){
            $sSelect .= " LIMIT ".$_GET['in'].",20";
          }
          $stmt = $this->db->prepare($sSelect);
          $stmt->bindParam("id", $_SESSION["user"]["CUENV_IDCUENTA"]);
          $stmt->bindParam("tipoprec", $_GET['tipoprec']);    
          $stmt->execute();      
          $data = $stmt->fetchAll();
        }        
                
        if($_GET['in'] != null){   
          return $response->withJson($data);
        }else{          
          $nRows = count($data);
          return $response->withJson(array(
            "id" => $nRows
          ));        
        }
    }    

    public function fetchProductImage(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `IMGV_IMAGEN1`, `IMGV_IMAGEN2`, `IMGV_IMAGEN3`, `IMGV_IMAGEN4`, `IMGV_IMAGEN5`
          FROM `tart_imagenes` WHERE `IMGV_IDARTICULO` = :id LIMIT 1");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $data = $stmt->fetch();

        //CONVERTIR A BASE64
        $data["IMGV_IMAGEN1"] = base64_encode($data["IMGV_IMAGEN1"]);
        $data["IMGV_IMAGEN2"] = base64_encode($data["IMGV_IMAGEN2"]);
        $data["IMGV_IMAGEN3"] = base64_encode($data["IMGV_IMAGEN3"]);
        $data["IMGV_IMAGEN4"] = base64_encode($data["IMGV_IMAGEN4"]);
        $data["IMGV_IMAGEN5"] = base64_encode($data["IMGV_IMAGEN5"]);

        return $response->withJson($data);
    }

    public function fetchGroups(Request $request, Response $response, $args)
    {
        $data = array();
        /*
         * ESTO HAY QUE CAMBIARLO PORQUE NO ME DIERON LA DATA DE LAS CATEGORIAS
         *
         * Tuve que sacarlas a mano a partir del producto y mostrarlas tal cual en la lista
         * la forma en que esta hecho el script va a servir porque filtro por el ID, solo
         * hay que retornar un nombre con INNER JOIN para que sea vea bien visualmente sin
         * dañar el funcionamiento
         */
        $stmt = $this->db->prepare("SELECT GRUCV_IDAGRUPAA value, GRUCV_NOMBRE descrip  FROM tagrupaa WHERE GRUCV_STATUS='1' order by GRUCV_NOMBRE");        
        $stmt->execute();      
        $data["GROUPAA"] = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT SUBGPV_IDAGRUPAA grupo, SUBGPV_IDAGRUPAB value, SUBGPV_NOMBRE descrip FROM tagrupab WHERE SUBGPV_IDAGRUPAA != '001' AND SUBGPV_IDAGRUPAA != '208' order by SUBGPV_NOMBRE");
        $stmt->execute();       
        $data["GROUPAB"] = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT TLINV_IDAGRUPAA grupo, TLINV_IDAGRUPAB subgrupo, TLINV_IDAGRUPAC value, TLINV_NOMBRE descrip FROM tagrupac WHERE 1 order by TLINV_NOMBRE");
        $stmt->execute();      
        $data["GROUPAC"] = $stmt->fetchAll();

        return $response->withJson($data);

    }
}
