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
		$('button[name=test]').click(function() {
			var url = OC.generateUrl('/apps/gpstracks/test/11');
			var data = {};
			var id=12;
			$.get(url, data).done(function(res){
				dispTrack(res);
				console.log(res);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});

		$('#app tbody').click(function(evt){
			var id = evt.target.getAttribute('trkid');
			var url = OC.generateUrl('/apps/gpstracks/gpx/'+id);
			$.get(url, {}).done(function(res){
console.log(res);
				dispTrack(res);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});

		tracklist();

		
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

	function pointlist() {
		
		
	}
	function dispTrack(track) {
		if(!OCA.OwnLayer) {
			alert('No Ownlayer');
			return ;
		}
		var last = track.coordinates[track.coordinates.length-1];
		OCA.OwnLayer.open(last);
		OCA.OwnLayer.plot('track', track);
	}

})(jQuery, OC);
