<?php
/**
 * Plugin Name: Orders Exporter
 * Description: Plugin untuk export orders
 * Version: 1.0
 * Author: Group I
 * Author URI: pbwl2020.com/groupi
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Versi WC yang digunakan: 4.0.1
 * source : https://www.codexworld.com/export-html-table-data-to-csv-using-javascript/
 */

//Untuk security
if (!defined('ABSPATH')) {
	exit;
}

function ordersExporter(){
	add_menu_page('Export', 'Export Orders', 'view_woocommerce_reports', 'orders-exporter', 'exportOrders', 'dashicons-media-spreadsheet', 25);
}

add_action('admin_menu', 'ordersExporter');

function exportOrders(){
	?>

	<html>
	<head>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>

	<?php 

	if (!current_user_can('view_woocommerce_reports')) {
		return;
	}

	global $wpdb;

	$query = $wpdb->get_results("

		SELECT
		ID,
		wp_postmeta.meta_key AS kolom,
		wp_postmeta.meta_value AS nilai,
		wp_woocommerce_order_itemmeta.order_item_id AS orderID,
		wp_woocommerce_order_itemmeta.meta_value AS value
		FROM
		wp_postmeta JOIN wp_posts ON 
		wp_postmeta.post_id = wp_posts.ID JOIN wp_woocommerce_order_items ON
		wp_woocommerce_order_items.order_id = wp_posts.ID JOIN wp_woocommerce_order_itemmeta ON
		wp_woocommerce_order_items.order_item_id = wp_woocommerce_order_itemmeta.order_item_id
		WHERE
		wp_postmeta.meta_key LIKE '_billing%' AND
		wp_postmeta.meta_key <> '_billing_address_index' AND
		wp_postmeta.meta_key <> '_billing_address_2' AND
		wp_woocommerce_order_itemmeta.meta_key LIKE '%Nama%'
		");
	// print_r($query);

		?> 
		<div class="mt-5 mb-3">
			<h5 class="text-center" >Daftar Peserta CHIPS 2019</h5>
		</div>
		<button class="btn btn-success" onclick="exportTableToCSV('data-pendaftar.csv')">Eksport data ke CSV</button>
		<input id="inputForm" type="text" placeholder="Search.." class="mb-3">
		<div>
			<table class="table table-striped w-100" id="tabelDownloadable">
				<thead class="thead-dark">
					<tr>
						<!-- <th width="10px">Nomor</th> -->
						<th>Nama Depan Guru</th>
						<th>Nama Belakang Guru</th>
						<th>Nama Sekolah</th>
						<th>Alamat Sekolah</th>
						<th>Kota / Kabupaten</th>
						<th>Provinsi</th>
						<th>Kode Pos</th>
						<th>e-Mail</th>
						<th>No. HP</th>
						<th>Nama Peserta 1</th>
						<th>Nama Peserta 2</th>
					</tr>
				</thead>

				<tbody id="bodyDownloadable">
					<?php 

					$i = 0;
					$nama = '';
					$id = '';
					$flag=true;
					echo "<tr>";
					foreach ($query as $key) {
					// print_r($key->ID);
						if($i==0){
							$nama = $key->value;
							$id = $key->ID;

						}

					// Kalo beda semua
						if($key->ID == $id AND $key->value != $nama and $flag == false ) {
							$nama = $key->value;
							$flag = true;
						}

					// Kalo ID dan nama nya sama, isi kesamping.
						if($key->ID == $id AND $key->value == $nama ){
							echo "<td>" . $key->nilai . "</td>";
						}

					// Kalo ID nya sama dan namanya beda, ambil peserta kedua
						else if ($key->ID == $id AND $key->value != $nama AND $flag == true) {
							echo "<td>" . $nama . "</td>";	
							echo "<td>" . $key->value . "</td>";	
							echo "</tr>";
							$id_int = (int)$id;
							$id_int += 1;
							$id = $id_int;
							$flag = false;
						}

						$i++;
					}
					?>
				</tbody>
			</table>

		</div>
		</html>
		<script>
			function downloadCSV(csv, filename) {
				var csvFile;
				var downloadLink;
				
			    // CSV file
			    csvFile = new Blob([csv], {type: "text/csv"});
			    // Download link
			    downloadLink = document.createElement("a");
			    // File name
			    downloadLink.download = filename;
			    // Create a link to the file
			    downloadLink.href = window.URL.createObjectURL(csvFile);
			    // Hide download link
			    downloadLink.style.display = "none";
			    // Add the link to DOM
			    document.body.appendChild(downloadLink);
			    // Click download link
			    downloadLink.click();
			}

			function exportTableToCSV(filename) {
				var csv = [];
				var rows = document.querySelectorAll("table tr");
				csv.push('sep=;')

				for (var i = 0; i < rows.length; i++) {
					var row = [], cols = rows[i].querySelectorAll("td, th");

					for (var j = 0; j < cols.length; j++){
						// var x = cols[j].innerText;
						// var newInnerText = x.replace(/,/g,' ');

						row.push(cols[j].innerText);
					}

					csv.push(row.join(";"));        
				}

		    // Download CSV file
		    downloadCSV(csv.join("\n"), filename);
		}

		$(document).ready(function(){
			$("#inputForm").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				$("#bodyDownloadable tr").filter(function() {
					$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
				});
			});
		});

		$(document).ready(function () {
			$('#tabelDownloadable').DataTable();
			$('.dataTables_length').addClass('bs-select');
		});
	</script>

	<?php
}
?>
