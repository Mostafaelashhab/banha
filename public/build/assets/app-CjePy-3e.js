(function(){let e=document.getElementById(`nav-progress`);e||(e=document.createElement(`div`),e.id=`nav-progress`,document.body.prepend(e));let t=()=>{e.classList.remove(`is-done`),e.classList.remove(`is-loading`),e.offsetWidth,e.classList.add(`is-loading`)};document.addEventListener(`click`,e=>{let n=e.target.closest(`a[href]`);if(!n)return;let r=n.getAttribute(`href`)||``;if(!r||r.startsWith(`#`)||r.startsWith(`javascript:`)||r.startsWith(`mailto:`)||r.startsWith(`tel:`)||r.startsWith(`whatsapp:`)||r.startsWith(`https://wa.me`)||n.target===`_blank`||n.hasAttribute(`download`)||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey||e.button!==0)return;let i=new URL(r,location.origin);i.origin===location.origin&&(i.pathname===location.pathname&&i.search===location.search||t())},!0),document.addEventListener(`submit`,e=>{e.target.dataset.noProgress!==`1`&&setTimeout(()=>t(),0)}),window.addEventListener(`pageshow`,()=>{e.classList.remove(`is-loading`),e.style.transform=`scaleX(0)`})})();var e={show(t,n={}){let r=document.createElement(`div`);r.className=`modal-wrap`,r.innerHTML=`
            <div class="modal-backdrop" data-close></div>
            <div class="modal-sheet ${n.size===`sm`?`modal-sm`:``}">${t}</div>
        `,document.body.appendChild(r),document.documentElement.classList.add(`overflow-hidden`),requestAnimationFrame(()=>r.classList.add(`open`));let i=t=>{t.key===`Escape`&&e.hide(r)};return document.addEventListener(`keydown`,i),r._cleanup=()=>document.removeEventListener(`keydown`,i),r.addEventListener(`click`,t=>{t.target.matches(`[data-close]`)&&e.hide(r)}),r},hide(e){e&&(e.classList.remove(`open`),e._cleanup?.(),document.documentElement.classList.remove(`overflow-hidden`),setTimeout(()=>e.remove(),220))},confirm({title:t,body:n=``,action:r=`تأكيد`,cancel:i=`إلغاء`,tone:a=`primary`}){return new Promise(o=>{let s=`
                <div class="p-5">
                    <h3 class="text-lg font-extrabold text-ink-950 mb-1">${t}</h3>
                    ${n?`<p class="text-ink-500 text-sm leading-relaxed">${n}</p>`:``}
                    <div class="flex gap-2 mt-5">
                        <button type="button" class="btn-ghost flex-1 justify-center" data-cancel>${i}</button>
                        <button type="button" class="${a===`danger`?`btn-dark`:`btn-primary`} flex-1 justify-center" data-ok>${r}</button>
                    </div>
                </div>`,c=e.show(s,{size:`sm`});c.querySelector(`[data-ok]`).onclick=()=>{e.hide(c),o(!0)},c.querySelector(`[data-cancel]`).onclick=()=>{e.hide(c),o(!1)},c.addEventListener(`click`,e=>{e.target.matches(`.modal-backdrop`)&&o(!1)})})}};window.banhawyModal=e,document.addEventListener(`submit`,async t=>{let n=t.target.closest(`form[data-confirm]`);!n||n.dataset.confirmed===`1`||(t.preventDefault(),await e.confirm({title:n.dataset.confirm,body:n.dataset.confirmBody||``,action:n.dataset.confirmAction||`تأكيد`,tone:n.dataset.confirmTone||`primary`})&&(n.dataset.confirmed=`1`,n.submit()))},!0);var t=[[`spam`,`سبام / إعلانات`,`بوست متكرر أو إعلاني`],[`abuse`,`إساءة / تنمر`,`شتيمة، تهديد، أو تنمر على حد`],[`nsfw`,`محتوى للكبار`,`صور/كلام جنسي أو عنف`],[`fake`,`خبر مزيف / إشاعة`,`معلومة كاذبة بتنشر هلع`],[`other`,`حاجة تانية`,`سبب مش موجود فوق`]];if(document.addEventListener(`click`,n=>{let r=n.target.closest(`[data-report]`);if(!r)return;n.preventDefault();let i=r.dataset.report,a=document.querySelector(`meta[name="csrf-token"]`)?.content;if(!i||!a)return;let o=`
        <form method="POST" action="${i}" class="p-5">
            <input type="hidden" name="_token" value="${a}">
            <h3 class="text-lg font-extrabold text-ink-950 mb-1">بلّغ عن البوست</h3>
            <p class="text-ink-500 text-sm mb-4">اختار السبب — التقارير اللي بتترفع كذب بتأثر على سمعتك.</p>

            <div class="max-h-[60vh] overflow-y-auto -mx-1 px-1">${t.map(([e,t,n])=>`
        <label class="flex items-start gap-3 p-3.5 rounded-2xl bg-cream-100 border border-ink-950/8 cursor-pointer has-[:checked]:bg-coral-100 has-[:checked]:border-coral-500/40 transition mb-2">
            <input type="radio" name="reason" value="${e}" class="peer mt-1 accent-coral-500" ${e===`spam`?`checked`:``}>
            <span class="flex-1">
                <span class="block font-bold text-ink-950 text-sm">${t}</span>
                <span class="block text-xs text-ink-500 mt-0.5">${n}</span>
            </span>
        </label>`).join(``)}</div>

            <textarea name="details" maxlength="500" rows="2" placeholder="تفاصيل إضافية (اختياري)…"
                class="mt-2 w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none text-sm"></textarea>

            <div class="flex gap-2 mt-4">
                <button type="button" class="btn-ghost flex-1 justify-center" data-close>إلغاء</button>
                <button type="submit" class="btn-primary flex-1 justify-center">أرسل البلاغ</button>
            </div>
        </form>`;e.show(o)}),setTimeout(()=>document.querySelectorAll(`[data-flash]`).forEach(e=>e.remove()),4500),(function(){let e=document.querySelector(`[data-infinite-scroll]`);if(!e||!(`IntersectionObserver`in window))return;let t=document.querySelector(`[data-feed-loader]`),n=document.querySelector(`[data-feed-done]`),r=!1,i=e.querySelector(`[data-feed-end]`)?.dataset.nextUrl||``,a=e.querySelector(`[data-feed-end]`)?.dataset.hasMore===`1`;if(!a){n?.classList.remove(`hidden`);return}let o=async()=>{if(!(r||!a||!i)){r=!0,t?.classList.remove(`hidden`);try{let t=new URL(i,location.origin);t.searchParams.set(`partial`,`1`);let r=await fetch(t,{headers:{"X-Requested-With":`XMLHttpRequest`,Accept:`text/html`},credentials:`same-origin`});if(!r.ok)throw Error(`Bad response`);let o=await r.text();e.querySelector(`[data-feed-end]`)?.remove();let c=document.createElement(`div`);for(c.innerHTML=o;c.firstChild;)e.appendChild(c.firstChild);let l=e.querySelector(`[data-feed-end]`);i=l?.dataset.nextUrl||``,a=l?.dataset.hasMore===`1`,a?s.observe(l):(n?.classList.remove(`hidden`),s.disconnect())}catch{s.disconnect(),n?.classList.remove(`hidden`)}finally{r=!1,t?.classList.add(`hidden`)}}},s=new IntersectionObserver(e=>{for(let t of e)t.isIntersecting&&o()},{rootMargin:`600px 0px 600px 0px`}),c=e.querySelector(`[data-feed-end]`);c&&s.observe(c)})(),`IntersectionObserver`in window){let e=new IntersectionObserver(t=>{t.forEach(t=>{t.isIntersecting&&(t.target.classList.add(`in`),e.unobserve(t.target))})},{threshold:.12});document.querySelectorAll(`.reveal`).forEach(t=>e.observe(t))}`serviceWorker`in navigator&&window.addEventListener(`load`,()=>{navigator.serviceWorker.register(`/sw.js`).catch(()=>{})});var n=/iPad|iPhone|iPod/.test(navigator.userAgent)&&!window.MSStream,r=window.matchMedia(`(display-mode: standalone)`).matches||window.navigator.standalone,i=`banhawy_install_dismissed_at`,a=null;window.addEventListener(`beforeinstallprompt`,e=>{e.preventDefault(),a=e,c()});function o(){let e=localStorage.getItem(i);return e&&Date.now()-Number(e)<10080*60*1e3}function s(){localStorage.setItem(i,String(Date.now())),document.getElementById(`install-banner`)?.remove()}function c(){if(r||o()||document.getElementById(`install-banner`))return;let e=document.createElement(`div`);e.id=`install-banner`,e.className=`install-banner`,e.innerHTML=`
        <div class="install-icon">
            <img src="/icons/icon-192.png" width="44" height="44" alt="بنهاوي">
        </div>
        <div class="install-text">
            <div class="install-title">حمّل بنهاوي</div>
            <div class="install-sub">افتحه في ثانية من الـ home screen</div>
        </div>
        <button class="install-cta" data-action="install">حمّل</button>
        <button class="install-close" data-action="dismiss" aria-label="إغلاق">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    `,document.body.appendChild(e),e.addEventListener(`click`,async e=>{let t=e.target.closest(`[data-action]`);t&&(t.dataset.action===`install`?(a&&=(a.prompt(),await a.userChoice,null),s()):t.dataset.action===`dismiss`&&s())})}function l(){o()||window.banhawyModal&&window.banhawyModal.show(`
        <div class="p-5">
            <h3 class="text-lg font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
                <img src="/icons/icon-192.png" width="32" height="32" alt="" class="rounded-lg">
                نزّل بنهاوي على iPhone
            </h3>
            <p class="text-ink-500 text-sm mb-5 leading-relaxed">عشان تفتحه زي أي تطبيق من الشاشة الرئيسية، اعمل الخطوات دي:</p>
            <ol class="space-y-3 text-sm text-ink-950">
                <li class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full brand-bg text-white grid place-items-center font-black shrink-0">1</span>
                    <span>اضغط على زر <b>المشاركة</b> في الـ Safari (المربّع بسهم لفوق <span class="text-coral-600 font-bold">⬆️</span>) من شريط الأدوات.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full brand-bg text-white grid place-items-center font-black shrink-0">2</span>
                    <span>اعمل scroll لتحت واختار <b>"Add to Home Screen"</b> (أضف إلى الشاشة الرئيسية).</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full brand-bg text-white grid place-items-center font-black shrink-0">3</span>
                    <span>اضغط <b>إضافة</b>، وهتلاقي بنهاوي بأيقونته على الـ home screen 🎉</span>
                </li>
            </ol>
            <div class="card-light !shadow-none border-coral-500/20 bg-coral-50 p-3 mt-5">
                <p class="text-xs text-ink-500">
                    <b class="text-ink-950">ملاحظة:</b>
                    لازم تستخدم <b>Safari</b> — الـ Chrome على iOS مش بيدعم Add to Home Screen.
                </p>
            </div>
            <div class="flex gap-2 mt-5">
                <button type="button" class="btn-ghost flex-1 justify-center" data-close>تمام</button>
            </div>
        </div>`)}function u(){r||o()||n&&setTimeout(()=>{document.querySelector(`.modal-wrap`)||l()},4e3)}window.banhawyInstall={showAndroid:()=>a?.prompt(),showIOS:l,maybeShow:u},document.body.dataset.installPrompt===`auto`&&u();var d=e=>{let t=`=`.repeat((4-e.length%4)%4),n=atob((e+t).replace(/-/g,`+`).replace(/_/g,`/`));return Uint8Array.from(n,e=>e.charCodeAt(0))};async function f(){if(!(`serviceWorker`in navigator)||!(`PushManager`in window))return{ok:!1,reason:`unsupported`};try{let e=await navigator.serviceWorker.ready;if(await Notification.requestPermission()!==`granted`)return{ok:!1,reason:`denied`};let{key:t}=await(await fetch(`/push/vapid`)).json();if(!t)return{ok:!1,reason:`no-vapid`};let n=await e.pushManager.subscribe({userVisibleOnly:!0,applicationServerKey:d(t)}),r=document.querySelector(`meta[name="csrf-token"]`)?.content;return await fetch(`/push/subscribe`,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":r},body:JSON.stringify(n)}),{ok:!0}}catch(e){return{ok:!1,reason:`error`,error:String(e)}}}async function p(){try{let e=await(await navigator.serviceWorker.ready).pushManager.getSubscription();if(!e)return{ok:!0};let t=document.querySelector(`meta[name="csrf-token"]`)?.content;return await fetch(`/push/unsubscribe`,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":t},body:JSON.stringify({endpoint:e.endpoint})}),await e.unsubscribe(),{ok:!0}}catch{return{ok:!1}}}window.banhawyPush={subscribe:f,unsubscribe:p},document.addEventListener(`click`,async e=>{let t=e.target.closest(`[data-push-toggle]`);if(!t)return;e.preventDefault(),t.disabled=!0;let n=t.dataset.pushOn===`1`,r=n?await p():await f();r.ok?(t.dataset.pushOn=n?`0`:`1`,t.textContent=n?`تشغيل التنبيهات`:`تنبيهات شغّالة ✓`):r.reason===`denied`?alert(`فعّل الإشعارات من إعدادات المتصفح علشان تستلم تنبيهات بنهاوي.`):r.reason===`unsupported`?alert(`متصفحك مش بيدعم Push notifications. استخدم Chrome أو Firefox الأحدث.`):r.reason===`no-vapid`&&alert(`Push notifications مش مفعّلين على السيرفر لسه.`),t.disabled=!1});function m(e){let t=document.createElement(`div`);t.className=`banhawy-toast`,t.textContent=e,document.body.appendChild(t),requestAnimationFrame(()=>t.classList.add(`in`)),setTimeout(()=>{t.classList.remove(`in`),setTimeout(()=>t.remove(),300)},2200)}document.addEventListener(`click`,async e=>{let t=e.target.closest(`[data-share]`);if(!t)return;e.preventDefault();let n=new URL(t.dataset.shareUrl||location.href,location.origin).href,r=t.dataset.shareTitle||`بنهاوي`,i=t.dataset.shareText||``,a=`${i?i+`

`:``}${n}\n\nمن بنهاوي 🔥`;if(navigator.share)try{await navigator.share({title:r,text:a,url:n});return}catch(e){if(e.name===`AbortError`)return}try{await navigator.clipboard.writeText(a),m(`✓ اللينك اتنسخ — جاهز للشير`)}catch{prompt(`انسخ اللينك:`,n)}});