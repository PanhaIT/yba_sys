// Convert numbers to words
// copyright 25th July 2006, by Stephen Chapman http://javascript.about.com
// permission to use this Javascript on your web page is granted
// provided that all of the code (including this copyright notice) is
// used exactly as shown (you can change the numbering system if you wish)

// American Numbering System
var th = ['','ពាន់','លាន','ពាន់លាន','ពាន់ពាន់លាន'];
// uncomment this line for English Number System
// var th = ['','thousand','million','milliard','billion'];

var dg = ['សូន្យ','មួយ','ពីរ','បី','បួន','ប្រាំ','ប្រាំមួយ','ប្រាំពីរ','ប្រាំបី','ប្រាំបួន']; var tn = ['ដប់','ដប់មួយ','ដប់ពីរ','ដប់បី','ដប់បួន','ដប់ប្រាំ','ដប់ប្រាំមួយ','ដប់ប្រាំពីរ','ដប់ប្រាំបី','ដប់ប្រាំបួន']; var tw = ['ម្ភៃ','សាមសិប','សែសិប','ហាសិប','ហុកសិប','ចិតសិប','ប៉ែតសិប','កៅសិប']; function toWords(s){s = s.replace(/[\, ]/g,''); if (s != parseFloat(s)) return 'មិនមែនជាលេខ'; var x = s.indexOf('.'); if (x == -1) x = s.length; if (x > 15) return 'ធំពេក'; var n = s.split(''); var str = ''; var sk = 0; for (var i=0; i < x; i++) {if ((x-i)%3==2) {if (n[i] == '1') {str += tn[Number(n[i+1])] + ' '; i++; sk=1;} else if (n[i]!=0) {str += tw[n[i]-2] + ' ';sk=1;}} else if (n[i]!=0) {str += dg[n[i]] +' '; if ((x-i)%3==0) str += 'រយ ';sk=1;} if ((x-i)%3==1) {if (sk) str += th[(x-i-1)/3] + ' ';sk=0;}} if (x != s.length) {var y = s.length; str += 'ក្បៀស '; for (var i=x+1; i<y; i++) str += dg[n[i]] +' ';} return str.replace(/\s+/g,'');}