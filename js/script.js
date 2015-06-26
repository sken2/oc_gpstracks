/**
 * ownCloud - gpstracks
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author shi <shi@example.com>
 * @copyright shi 2015
 */

(function ($, OC) {

	$(document).ready(function () {
		$('button[name=get26]').click(function(){
			var url = OC.generateUrl('/apps/gpstracks/gpx/26');
			var data = {};
			$.get(url, data).done(function(res){
				console.log(res);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});
		$('button[name=get261]').click(function(){
			var url = OC.generateUrl('/apps/gpstracks/gpx/26/0');
			var data = {};
			$.ajax({url:url, data:data, type:'post'}).done(function(res){
				console.log(res);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});

		$('button[name=test]').click(function() {
			var url = OC.generateUrl('/apps/gpstracks/test/1435031254');
			var data = {};
			$.get(url, data).done(function(res){
				console.log(res);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});
//OSM
//		(function() {
//			var map = new OpenLayers.Map("canvas");
//			var mapnik = new OpenLayers.Layer.OSM();
//			map.addLayer(mapnik);
//
//			var lonLat = new OpenLayers.Lonlat(139.76, 35.68)
//				.transform(
//					new OpenLayers.Projection("EPSG:4326"),
//					new OpenLayers.Projection("EPSG:900913")
//				);	
//			map.setCenter(lonLat, 15);
//		})();
	});

})(jQuery, OC);