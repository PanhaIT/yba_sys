var layoutState={options:{layoutName:'myLayout',keys:'north__size,south__size,east__size,west__size,'+'north__isClosed,south__isClosed,east__isClosed,west__isClosed,'+'north__isHidden,south__isHidden,east__isHidden,west__isHidden',domain:'',path:'',expires:'',secure:false},data:{},clear:function(layoutName){this.save(layoutName,'dummyKey',{expires:-1});},save:function(layoutName,keys,opts){var
o=jQuery.extend({},this.options,opts||{}),layout=window[layoutName||o.layoutName];if(!keys)keys=o.keys;if(typeof keys=='string')keys=keys.split(',');if(!layout||!layout.state||!keys.length)return false;var
isNum=typeof o.expires=='number',date=new Date(),params='',clear=false;if(isNum||o.expires.toUTCString){if(isNum){if(o.expires<=0){date.setYear(1970);clear=true;}
else
date.setTime(date.getTime()+(o.expires*24*60*60*1000));}
else
date=o.expires;params+=';expires='+date.toUTCString();}
if(o.path)params+=';path='+o.path;if(o.domain)params+=';domain='+o.domain;if(o.secure)params+=';secure';if(clear){this.data={};document.cookie=(layoutName||o.layoutName)+'='+params;}
else{this.data=readState(layout,keys);document.cookie=(layoutName||o.layoutName)+'='+encodeURIComponent(JSON.stringify(this.data))+params;}
return this.data;function readState(layout,keys){var
state=layout.state,data={},panes='north,south,east,west,center',alt={isClosed:'initClosed',isHidden:'initHidden'},delim=(keys[0].indexOf('__')>0?'__':'.'),pair,pane,key,val;for(var i=0;i<keys.length;i++){pair=keys[i].split(delim);pane=pair[0];key=pair[1];if(panes.indexOf(pane)<0)continue;if(key=='isClosed')
val=state[pane][key]||state[pane]['isSliding'];else
val=state[pane][key];if(val!=undefined){if(delim=='.'){if(!data[pane])data[pane]={};data[pane][alt[key]?alt[key]:key]=val;}
else
data[pane+delim+(alt[key]?alt[key]:key)]=val;}}
return data;}},load:function(layoutName){if(!layoutName)layoutName=this.options.layoutName;if(!layoutName)return{};var
data={},c=document.cookie,cs,pair,i;if(c&&c!=''){cs=c.split(';');for(i=0;i<cs.length;i++){c=jQuery.trim(cs[i]);pair=c.split('=');if(pair[0]==layoutName){data=JSON.parse(decodeURIComponent(pair[1]));break;}}}
return(this.data=data);}}