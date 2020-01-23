<?php


	function readCSV($csvFile)
	{
	    $file_handle = fopen($csvFile, 'r');
	    while (!feof($file_handle) ) 
	    {
	        $line_of_text[] = fgetcsv($file_handle, 1024, ';');
	    }
	    fclose($file_handle);
	    return $line_of_text;
	}

	function formatMoney($money)
	{
		$money = str_replace('.','',$money);
		$money = str_replace('R$','',$money);
		$money = trim($money);
		$_money = (int) $money;
		if( $_money < 0 )
		{
			$_money = abs($_money);
			$money = "<span class='valor-negativo'>- " . number_format($_money, 2, ',', '.') . "</span>";
		}
		else
		{
			$money = 'R$ '. number_format($money, 2, ',', '.');
		}
		return $money;
	}
	 
	
	$csvFile = 'fiscalize/fiscalize.csv';
	$csv = readCSV($csvFile);

	$htmlTable = "<div class='table-fiscalize-mobile'><table class='table-fiscalize'><thead>";

	/// dados
	$fornecedores = array();
	$centros = array();
	$mesAno = array();
	///

	/// filtros
	if( isset($_GET[2]) && $_GET[2] != "" )
	{
		$colFilter2 = $_GET[2];
	}
	if( isset($_GET[5]) && $_GET[5] != "" )
	{
		$colFilter5 = $_GET[5];
	}
	if( isset($_GET[1]) && $_GET[1] != "" )
	{
		$colFilter1 = $_GET[1];
	}
	


	///


	foreach( $csv as $i => $line )
	{

		if( isset($colFilter2) && $i != 0 )
		{
			if( $colFilter2 != $line[2] )
			{
				$htmlTable .= "<tr style='display: none;'>";
			}
		}
		else if( isset($colFilter5) && $i != 0 )
		{
			if( $colFilter5 != $line[5] )
			{
				$htmlTable .= "<tr style='display: none;'>";
			}
		}
		else if( isset($colFilter1) && $i != 0 )
		{
			if( $colFilter1 != substr($line[1],3) )
			{
				$htmlTable .= "<tr style='display: none;'>";
			}
		}
		else
		{
			$htmlTable .= "<tr>";
		}
		

		if( is_array($line) )
		{
			foreach( $line as $key => $row )
			{
				if( $i != 0 )
				{
					$row = $key == 4 ? formatMoney($row) : $row;
					$row = $key == 6 ? formatMoney($row) : $row;
					$key == 2 && array_push($fornecedores, $row);
					$key == 5 && array_push($centros, $row);
					$key == 1 && array_push($mesAno,substr($row, 3));
				}	

				$htmlTable .= sprintf("<td>%s</td>",$row);	
				
				
			}
		}
		

		$fornecedores = array_unique($fornecedores);
		$centros = array_unique($centros);
		$mesAno = array_unique($mesAno);

		if( $i == 0 )
		{
			$htmlTable .= "</tr></thead>";
		}
		else if( $i == 1 )
		{
			$htmlTable .= "</tr><tbody>";
		}
		else
		{
			$htmlTable .= "</tr>";
		}

	}
	
	$htmlTable .= "</tbody></table></div>";

	$htmlFiltros = "<form>";
	foreach ($mesAno as $i => $mes) 
	{
		if( $i == 0 )
		{
			$htmlFiltros .= "<span class='filter'>Mês: <select name='1'><option value=''>-</option>";
		}
		$selected = "";
		if( isset($colFilter1) && $colFilter1 == $mes )
		{
			$selected = 'selected';
		}
		$htmlFiltros .= sprintf("<option %s value='%s'>%s</option>",$selected,$mes,$mes);
	}
	!empty($mesAno) && $htmlFiltros .= "</select></span>";
	//
	foreach ($fornecedores as $i => $fornecedor) 
	{
		if( $i == 0 )
		{
			$htmlFiltros .= "<span class='filter'>Fornecedor: <select name='2'><option value=''>-</option>";
		}
		$selected = "";
		if( isset($colFilter2) && $colFilter2 == $fornecedor )
		{
			$selected = 'selected';
		}
		$htmlFiltros .= sprintf("<option %s value='%s'>%s</option>",$selected,$fornecedor,$fornecedor);
	}
	!empty($fornecedores) && $htmlFiltros .= "</select></span>";
	//
	foreach ($centros as $i => $centro) 
	{
		if( $i == 0 )
		{
			$htmlFiltros .= "<span class='filter'>Centro de Custo: <select name='5'><option value=''>-</option>";
		}
		$selected = "";
		if( isset($colFilter5) && $colFilter5 == $centro )
		{
			$selected = 'selected';
		}
		$htmlFiltros .= sprintf("<option %s value='%s'>%s</option>",$selected,$centro,$centro);
	}
	!empty($centros) && $htmlFiltros .= "</select></span>";
	$htmlFiltros .= "</form>";

	echo $htmlFiltros;

	echo $htmlTable;

	