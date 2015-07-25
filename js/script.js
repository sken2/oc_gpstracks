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
//				dispTrack(res);
				console.log(res);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});
		$('#trklist').click(function() {
			clear_content();
			tracklist();
		});
		$('#refresh').click(function() {
			clear_content();
			reload();
			tracklist();
		});

		$('#app-content').click(function(evt){
			var id = evt.target.getAttribute('trkid');
			var url = OC.generateUrl('/apps/gpstracks/gpx/'+id);
			var trkname = evt.target.firstChild.nodeValue;
			!id ||$.get(url, {}).done(function(res){
console.log(res);
				dispTrack(res, trkname);
			}).fail(function(xhr){
				console.log(xhr);
			});
		});

		tracklist();

		
	});
	function tracklist(){
		var url = OC.generateUrl('/apps/gpstracks/gpx');
		$.get(url, {}).done(function(trk){
//			clear_content();
			trk.forEach(function(trkinfo){
				var li = document.createElement('LI');
				li.appendChild(document.createTextNode(trkinfo.name));
				li.setAttribute('trkid', trkinfo.id);
				$('#app-content ul').append(li);
			});
			console.log(trk);
		}).fail(function(xhr){
			console.log("Ajax to "+url+" failed");
		});
	}
	function reload(){
		var url = OC.generateUrl('/apps/gpstracks/gpx');
		$.post(url, {}).done(function() {
			;//well done
		}).fail(function(xhr){
			console.log(xhr);
		});
	}
	function pointlist() {
		
		
	}
	function dispTrack(track, trkname) {
		trkname = trkname || 'track';
		if(!OCA.OwnLayer) {
			alert('No Ownlayer');
			return ;
		}
		var last = track.coordinates[track.coordinates.length-1];
		OCA.OwnLayer.open(last);
		OCA.OwnLayer.plot(trkname, track);
	}
	function clear_content() {
		$('#app-content-wrapper ul').each(function(){
			while(this.firstChild){
				this.removeChild(this.firstChild);
			}
		});
	}

})(jQuery, OC);
