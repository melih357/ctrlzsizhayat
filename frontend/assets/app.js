function toggleMenu(){document.getElementById('menu')?.classList.toggle('open')}
function togglePassword(id){const x=document.getElementById(id); if(!x)return; x.type=x.type==='password'?'text':'password'}
function onlyDigits(el,max){el.value=el.value.replace(/\D/g,'').slice(0,max)}
function priceInput(el){el.value=el.value.replace(/[^0-9.,]/g,'').replace(',','.');}
function showTab(id){document.querySelectorAll('.admin-section').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));document.getElementById(id)?.classList.add('active');document.querySelector('[data-tab="'+id+'"]')?.classList.add('active')}
function changeMain(src){document.getElementById('mainpic').src=src}
function openImage(src){const m=document.getElementById('imgModal'); if(m){m.style.display='flex';document.getElementById('modalImg').src=src}}
function closeImage(){const m=document.getElementById('imgModal'); if(m)m.style.display='none'}
function updateCheckoutTotal(totalTry){const cur=document.getElementById('checkoutCurrency')?.value||'TRY';const r=(window.fxRates&&window.fxRates[cur])||1;const val=totalTry*r;const el=document.getElementById('checkoutTotal');if(!el)return; if(cur==='TRY')el.textContent=val.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2})+' TL'; if(cur==='USD')el.textContent='$'+val.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); if(cur==='EUR')el.textContent='€'+val.toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2});}

function currencyName(cur){return cur==='USD'?'Dolar / USD':(cur==='EUR'?'Euro / EUR':'TL');}
function syncCurrencyLabel(){const cur=document.getElementById('checkoutCurrency')?.value||'TRY'; const el=document.getElementById('selectedCurrencyLabel'); if(el)el.textContent=currencyName(cur);}
function confirmCheckoutCurrency(){const cur=document.getElementById('checkoutCurrency')?.value||'TRY'; const total=document.getElementById('checkoutTotal')?.textContent||''; return confirm('Satın alma para birimi '+currencyName(cur)+' ve tutar '+total+' olarak görünüyor. Devam etmek istiyor musun?');}

const fallbackDistricts={
"İstanbul":["Adalar","Arnavutköy","Ataşehir","Avcılar","Bağcılar","Bahçelievler","Bakırköy","Başakşehir","Bayrampaşa","Beşiktaş","Beykoz","Beylikdüzü","Beyoğlu","Büyükçekmece","Çatalca","Çekmeköy","Esenler","Esenyurt","Eyüpsultan","Fatih","Gaziosmanpaşa","Güngören","Kadıköy","Kağıthane","Kartal","Küçükçekmece","Maltepe","Pendik","Sancaktepe","Sarıyer","Silivri","Sultanbeyli","Sultangazi","Şile","Şişli","Tuzla","Ümraniye","Üsküdar","Zeytinburnu"],
"Ankara":["Altındağ","Beypazarı","Çankaya","Etimesgut","Gölbaşı","Keçiören","Mamak","Polatlı","Pursaklar","Sincan","Yenimahalle"],
"İzmir":["Bornova","Buca","Çeşme","Çiğli","Gaziemir","Karabağlar","Karşıyaka","Konak","Menemen","Torbalı","Urla"],
"Bursa":["Gemlik","İnegöl","İznik","Mudanya","Nilüfer","Osmangazi","Yıldırım"],
"Antalya":["Alanya","Kepez","Konyaaltı","Manavgat","Muratpaşa","Serik"],
"default":["Merkez"]
};
const fallbackNeighborhoods={
"İstanbul|Pendik":["Ahmet Yesevi","Bahçelievler","Batı","Çamçeşme","Çınardere","Doğu","Ertuğrul Gazi","Esenler","Fevzi Çakmak","Güzelyalı","Kaynarca","Kurtköy","Orhangazi","Sülüntepe","Velibaba","Yenişehir"],
"İstanbul|Kadıköy":["Acıbadem","Bostancı","Caddebostan","Caferağa","Erenköy","Fenerbahçe","Feneryolu","Göztepe","Koşuyolu","Moda","Osmanağa","Suadiye"],
"İstanbul|Üsküdar":["Altunizade","Beylerbeyi","Çengelköy","Kandilli","Kısıklı","Kuzguncuk","Mimar Sinan","Selami Ali","Valide-i Atik"],
"Ankara|Çankaya":["Ayrancı","Bahçelievler","Birlik","Çukurambar","Kavaklıdere","Kızılay","Oran","Yıldız"],
"İzmir|Konak":["Alsancak","Basmane","Göztepe","Güzelyalı","Hatay","Kahramanlar","Kemeraltı"],
"default":["Merkez Mahallesi","Cumhuriyet Mahallesi","Yeni Mahalle","Fatih Mahallesi","Atatürk Mahallesi"]
};
const provinces=["Adana","Adıyaman","Afyonkarahisar","Ağrı","Amasya","Ankara","Antalya","Artvin","Aydın","Balıkesir","Bilecik","Bingöl","Bitlis","Bolu","Burdur","Bursa","Çanakkale","Çankırı","Çorum","Denizli","Diyarbakır","Edirne","Elazığ","Erzincan","Erzurum","Eskişehir","Gaziantep","Giresun","Gümüşhane","Hakkari","Hatay","Isparta","Mersin","İstanbul","İzmir","Kars","Kastamonu","Kayseri","Kırklareli","Kırşehir","Kocaeli","Konya","Kütahya","Malatya","Manisa","Kahramanmaraş","Mardin","Muğla","Muş","Nevşehir","Niğde","Ordu","Rize","Sakarya","Samsun","Siirt","Sinop","Sivas","Tekirdağ","Tokat","Trabzon","Tunceli","Şanlıurfa","Uşak","Van","Yozgat","Zonguldak","Aksaray","Bayburt","Karaman","Kırıkkale","Batman","Şırnak","Bartın","Ardahan","Iğdır","Yalova","Karabük","Kilis","Osmaniye","Düzce"];
const cache={districts:{}, neighborhoods:{}};
function addOptions(sel,arr,placeholder){sel.innerHTML=''; if(placeholder)sel.add(new Option(placeholder,'')); [...new Set(arr)].sort((a,b)=>a.localeCompare(b,'tr')).forEach(v=>sel.add(new Option(v,v)));}
function fillProvinces(){document.querySelectorAll('.province').forEach(sel=>{if(sel.dataset.filled)return; addOptions(sel,provinces,'İl seçiniz'); sel.dataset.filled=1; if(!sel.value) sel.value='İstanbul'; updateDistrict(sel);});}
async function fetchDistricts(prov){
 if(cache.districts[prov])return cache.districts[prov];
 try{const r=await fetch('https://turkiyeapi.dev/api/v1/districts?province='+encodeURIComponent(prov)+'&fields=name&limit=120');const j=await r.json();const arr=(j.data||[]).map(x=>x.name).filter(Boolean); if(arr.length){cache.districts[prov]=arr;return arr;}}catch(e){}
 return fallbackDistricts[prov]||fallbackDistricts.default;
}
async function fetchNeighborhoods(prov,dist){
 const key=prov+'|'+dist; if(cache.neighborhoods[key])return cache.neighborhoods[key];
 try{const r=await fetch('https://turkiyeapi.dev/api/v1/neighborhoods?province='+encodeURIComponent(prov)+'&district='+encodeURIComponent(dist)+'&fields=name&limit=500');const j=await r.json();const arr=(j.data||[]).map(x=>x.name).filter(Boolean); if(arr.length){cache.neighborhoods[key]=arr;return arr;}}catch(e){}
 return fallbackNeighborhoods[key]||fallbackNeighborhoods.default;
}
async function updateDistrict(provSel){const district=document.querySelector(provSel.dataset.district||provSel.dataset.target); if(!district)return; const arr=await fetchDistricts(provSel.value); addOptions(district,arr,'İlçe seçiniz'); if(arr.length)district.value=arr[0]; district.dataset.province=provSel.value; district.dataset.neighborhood=provSel.dataset.neighborhood||''; updateNeighborhood(district);}
async function updateNeighborhood(distSel){const nsel=document.querySelector(distSel.dataset.neighborhood||'#neighborhood'); if(!nsel)return; const prov=distSel.dataset.province || document.querySelector('.province')?.value || ''; const arr=await fetchNeighborhoods(prov,distSel.value); addOptions(nsel,arr,'Mahalle seçiniz'); if(arr.length)nsel.value=arr[0];}
document.addEventListener('DOMContentLoaded',()=>{fillProvinces(); if(location.hash){const id=location.hash.slice(1); if(document.getElementById(id)?.classList.contains('admin-section')) showTab(id);} const c=document.getElementById('checkoutCurrency'); if(c&&window.fxRates){ if(window.checkoutInitialCurrency) c.value=window.checkoutInitialCurrency; const total=parseFloat(document.querySelector('[data-total-try]')?.dataset.totalTry||0); if(total>0) updateCheckoutTotal(total); syncCurrencyLabel(); }});
