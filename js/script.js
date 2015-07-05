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
		
//		$('button[name=get26]').click(function(){
//			var url = OC.generateUrl('/apps/gpstracks/gpx/26');
//			var data = {};
//			$.get(url, data).done(function(res){
//				console.log(res);
//			}).fail(function(xhr){
//				console.log(xhr);
//			});
//		});
//
//		$('button[name=get261]').click(function(){
//			var url = OC.generateUrl('/apps/gpstracks/gpx/26/0');
//			var data = {};
//			$.ajax({url:url, data:data, type:'post'}).done(function(res){
//				console.log(res);
//			}).fail(function(xhr){
//				console.log(xhr);
//			});
//		});

		$('button[name=test]').click(function() {
			var url = OC.generateUrl('/apps/gpstracks/test/11');
			var data = {};
			$.get(url, data).done(function(res){
				console.log(res);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});

		$('#app tbody').click(function(evt){
			var id = evt.target.getAttribute('trkid');
			var url = OC.generateUrl('/apps/gpstracks/gpx/'+id);
console.log(url);
//			$.get(url, {}).done(function(res){
//				console.log(res);
				dispTrack(id);
//			}).fail(function(xhr){
//				console.log(xhr);
//			});
		});

		function tracklist(){
			var url = OC.generateUrl('/apps/gpstracks/gpx');
			$.get(url, {}).done(function(trk){
				$('#app tbody').each(function () {
					while(this.firstChild){
						this.removeChild(this.firstChild);
					}
				});
				trk.forEach(function(trkinfo){
					var tr = document.createElement('TR');
					tr.appendChild(document.createTextNode(trkinfo.name));
					tr.setAttribute('trkid', trkinfo.id);
					$('#app tbody').append(tr);
				});
				console.log(trk);
			}).fail(function(xhr){
				console.log("Ajax to "+url+" failed");
			});
		}
		tracklist();
	});
	function dispTrack(id) {
		if(!OCA.OwnLayer) {
			alert('No Ownlayer');
			return ;
		}
		var url = OC.generateUrl('/apps/gpstracks/gpx/'+id);
		var track = new ol.source.Vector({//!
			url: url,
			format: new ol.format.GPX()
		});
		var vector = new ol.layer.Vector({
			source: track
		});
		OCA.OwnLayer.open();
		OCA.OwnLayer.Map.addLayer(vector);
	}

})(jQuery, OC);
