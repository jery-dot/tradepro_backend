/* ============================================================
   TradePro Admin Panel — admin.js
   All data, render functions, filters, detail panels, modals
   ============================================================ */

'use strict';

// ── Type helpers ──────────────────────────────────────────────────────────────
const TC = { contractor: '#1B3D6F', subcontractor: '#F5874F', labour: '#27AE60', apprentice: '#8E44AD' };
const TL = { contractor: 'Contractor', subcontractor: 'Sub-contractor', labour: 'Labour', apprentice: 'Apprentice' };
const TB = { contractor: 'bn', subcontractor: 'bo', labour: 'bg', apprentice: 'bp' };

function ini(name) { return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase(); }
function stars(n)  { const f = Math.round(n); return '★'.repeat(f) + '☆'.repeat(5 - f); }

// ── Toast ─────────────────────────────────────────────────────────────────────
function toast(msg, type = '') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast show' + (type ? ' ' + type : '');
    setTimeout(() => { el.className = 'toast'; }, 3200);
}

// ── DATA ──────────────────────────────────────────────────────────────────────
const USERS = [
    { id:1,  name:'James Holt',    email:'james.h@tradie.com',   type:'contractor',    city:'San Francisco', state:'CA', trade:'Carpentry, Roofing',   exp:'8 years',   insured:'Yes', bg:'Cleared',  followers:142, following:38,  plan:'Contractor $59.99',     available:true,  gender:'Male',   apprentice:false, status:'active'    },
    { id:2,  name:'Emily Watson',  email:'emily.w@tradie.com',   type:'apprentice',    city:'Los Angeles',   state:'CA', trade:'Carpentry',             exp:'0-1 years', insured:'No',  bg:'Pending',  followers:24,  following:12,  plan:'Apprentice $9.99',      available:true,  gender:'Female', apprentice:true,  status:'active'    },
    { id:3,  name:'Sahil Mehta',   email:'sahil.m@tradie.com',   type:'contractor',    city:'Houston',       state:'TX', trade:'Electrical, Plumbing',  exp:'12 years',  insured:'Yes', bg:'Cleared',  followers:310, following:55,  plan:'Contractor $59.99',     available:true,  gender:'Male',   apprentice:false, status:'active'    },
    { id:4,  name:'Maria Lopez',   email:'maria.l@tradie.com',   type:'labour',        city:'Dallas',        state:'TX', trade:'General Labour',        exp:'3 years',   insured:'No',  bg:'Cleared',  followers:18,  following:9,   plan:'Labourer $19.99',       available:false, gender:'Female', apprentice:false, status:'active'    },
    { id:5,  name:'Derek Owens',   email:'derek.o@tradie.com',   type:'subcontractor', city:'Austin',        state:'TX', trade:'Framing, Concrete',     exp:'6 years',   insured:'Yes', bg:'Cleared',  followers:88,  following:22,  plan:'Contractor $59.99',     available:true,  gender:'Male',   apprentice:false, status:'active'    },
    { id:6,  name:'John Doe',      email:'jdoe@tradie.com',      type:'contractor',    city:'Las Vegas',     state:'NV', trade:'Roofing',               exp:'5 years',   insured:'Yes', bg:'Flagged',  followers:14,  following:7,   plan:'Contractor $59.99',     available:false, gender:'Male',   apprentice:false, status:'suspended' },
    { id:7,  name:'Priya Sharma',  email:'priya.s@tradie.com',   type:'apprentice',    city:'Seattle',       state:'WA', trade:'Electrical',            exp:'0-1 years', insured:'No',  bg:'Pending',  followers:9,   following:5,   plan:'Apprentice $9.99',      available:true,  gender:'Female', apprentice:true,  status:'active'    },
    { id:8,  name:'Marcus Webb',   email:'marcus.w@tradie.com',  type:'labour',        city:'Denver',        state:'CO', trade:'Concrete, Masonry',     exp:'4 years',   insured:'No',  bg:'Cleared',  followers:31,  following:14,  plan:'Labourer $19.99',       available:false, gender:'Male',   apprentice:false, status:'inactive'  },
    { id:9,  name:'Chen Liu',      email:'chen.l@tradie.com',    type:'subcontractor', city:'Chicago',       state:'IL', trade:'Plumbing, HVAC',        exp:'9 years',   insured:'Yes', bg:'Cleared',  followers:205, following:67,  plan:'Contractor $59.99',     available:true,  gender:'Male',   apprentice:false, status:'active'    },
    { id:10, name:'Aisha Brown',   email:'aisha.b@tradie.com',   type:'apprentice',    city:'Houston',       state:'TX', trade:'Roofing',               exp:'0-1 years', insured:'No',  bg:'Pending',  followers:6,   following:3,   plan:'Apprentice Free Trial', available:true,  gender:'Female', apprentice:true,  status:'active'    },
];

const JOBS = [
    { id:1,  title:'Carpenter — Frame Build',        company:'ABC Construction',  compRating:4.8, type:'contractor',    skills:['Carpentry','Framing','Roofing'],    pay:35.5, loc:'San Francisco, CA', duration:'2 months', start:'01 Dec 2025', desc:'Seeking experienced carpenter for new house construction.',   featured:false, available:true,  status:'open'      },
    { id:2,  title:'Site Safety Coordinator',        company:'BuildSafe Inc',     compRating:4.5, type:'subcontractor', skills:['Electrical','Concrete'],            pay:45,   loc:'Los Angeles, CA',   duration:'6 months', start:'15 Dec 2025', desc:'Need certified safety officer for commercial building site.', featured:true,  available:true,  status:'open'      },
    { id:3,  title:'Electrical Wiring — Office',     company:'PowerTech LLC',     compRating:4.2, type:'subcontractor', skills:['Electrical','Plumbing'],            pay:50,   loc:'Houston, TX',       duration:'3 months', start:'20 Dec 2025', desc:'Full electrical fit-out for 5-storey office complex.',        featured:false, available:true,  status:'pending'   },
    { id:4,  title:'Demolition Crew Needed',         company:'FastBuild Co',      compRating:3.9, type:'labour',        skills:['General Labour'],                   pay:25,   loc:'Dallas, TX',        duration:'1 month',  start:'10 Jan 2026', desc:'8 labourers required for commercial demolition project.',     featured:false, available:true,  status:'open'      },
    { id:5,  title:'Scaffolding Contractor',         company:'SkyHigh Ltd',       compRating:4.6, type:'contractor',    skills:['Framing','Carpentry'],              pay:38,   loc:'Austin, TX',        duration:'4 months', start:'01 Jan 2026', desc:'Experienced scaffolding contractor for warehouse build.',     featured:true,  available:false, status:'open'      },
    { id:6,  title:'HVAC Installation Team',         company:'CoolAir Systems',   compRating:4.7, type:'subcontractor', skills:['Plumbing','Electrical'],            pay:55,   loc:'Phoenix, AZ',       duration:'2 months', start:'05 Jan 2026', desc:'HVAC installation in 200-unit residential complex.',          featured:false, available:true,  status:'completed' },
    { id:7,  title:'Concrete Finishers',             company:'SolidBase Inc',     compRating:4.1, type:'labour',        skills:['Concrete','General Labour'],        pay:28,   loc:'Denver, CO',        duration:'6 weeks',  start:'15 Jan 2026', desc:'Concrete finishing for large warehouse slab.',               featured:false, available:true,  status:'open'      },
    { id:8,  title:'Roofing Crew Lead',              company:'TopCover Co',       compRating:4.4, type:'contractor',    skills:['Roofing','Framing'],                pay:42,   loc:'Seattle, WA',       duration:'3 months', start:'20 Jan 2026', desc:'Lead a crew of 6 roofers on residential subdivision.',       featured:true,  available:true,  status:'open'      },
    { id:9,  title:'Residential Plumbing',           company:'FlowPro Ltd',       compRating:4.3, type:'subcontractor', skills:['Plumbing'],                         pay:48,   loc:'Chicago, IL',       duration:'8 weeks',  start:'01 Feb 2026', desc:'Full rough-in plumbing for 40 townhomes.',                   featured:false, available:false, status:'pending'   },
    { id:10, title:'Painting Subcontractor',         company:'ColorPro Inc',      compRating:4.0, type:'subcontractor', skills:['Painting','General Labour'],        pay:30,   loc:'Miami, FL',         duration:'3 months', start:'15 Feb 2026', desc:'Interior and exterior painting for luxury apartment.',       featured:false, available:true,  status:'closed'    },
];

const APPRENTICESHIPS = [
    { id:1, company:'RusCon Company',    logo:'RC', trade:'Carpentry, Framing, Roofing', comp:'$33.5/hr', loc:'San Francisco, CA', start:'Dec 2025', duration:'2 years', questions:3, applicants:5,  pricing:'Contractor/Sub $59.99', about:'RusCon has been serving the Bay Area for 20 years.',                  requirements:'Must be available M-F, reliable transport.',       status:'open'   },
    { id:2, company:'BuildRight LLC',    logo:'BR', trade:'Electrical, Plumbing',         comp:'$28/hr',   loc:'Los Angeles, CA',   start:'Jan 2026', duration:'3 years', questions:2, applicants:8,  pricing:'Contractor/Sub $59.99', about:'Specialising in commercial electrical and plumbing.',         requirements:'High school diploma or equivalent.',                status:'open'   },
    { id:3, company:'SkyHigh Construction',logo:'SH',trade:'Roofing, Framing',             comp:'$30/hr',   loc:'Houston, TX',       start:'Feb 2026', duration:'2 years', questions:4, applicants:3,  pricing:'Apprentice FREE',       about:'Leading roofing contractor in Texas.',                        requirements:'Must be comfortable with heights.',                  status:'open'   },
    { id:4, company:'PowerBuild Inc',    logo:'PB', trade:'Electrical, HVAC',             comp:'$35/hr',   loc:'Dallas, TX',        start:'Mar 2026', duration:'4 years', questions:3, applicants:12, pricing:'Contractor/Sub $59.99', about:'Full electrical and HVAC contractor operating since 2005.',  requirements:'Vocational training preferred.',                     status:'open'   },
    { id:5, company:'CityWorks Co',      logo:'CW', trade:'Concrete, Masonry',            comp:'$25/hr',   loc:'Austin, TX',        start:'Jan 2026', duration:'2 years', questions:1, applicants:6,  pricing:'Apprentice FREE',       about:'Municipal and commercial concrete specialists.',             requirements:'Physical fitness required.',                         status:'closed' },
];

const APPLICANTS = [
    { id:1, name:'Emily Watson',  trade:'Carpentry',       age:22, loc:'San Francisco, CA', edu:'High School Diploma',    about:'Passionate about woodworking since age 14. Eager to learn all aspects of the trade.', resume:'emily_resume.pdf',   applied:'RusCon Company',    visible:true,  status:'pending'  },
    { id:2, name:'Jake Morrison', trade:'Electrical',      age:20, loc:'Los Angeles, CA',   edu:'Vocational Certificate', about:'Completed basic electrical theory. Looking for hands-on experience.',                  resume:'jake_resume.pdf',    applied:'BuildRight LLC',    visible:true,  status:'accepted' },
    { id:3, name:'Aisha Brown',   trade:'Roofing',         age:19, loc:'Houston, TX',       edu:'High School Diploma',    about:'Strong work ethic and not afraid of heights. Ready to start immediately.',              resume:'',                   applied:'SkyHigh Construction', visible:false, status:'pending'  },
    { id:4, name:'Leo Chen',      trade:'Plumbing',        age:21, loc:'Dallas, TX',        edu:'Some College',           about:'Studied mechanical engineering for 1 year. Switching to practical trades.',             resume:'leo_resume.pdf',     applied:'BuildRight LLC',    visible:true,  status:'rejected' },
    { id:5, name:'Zara Ahmed',    trade:'Electrical, HVAC',age:23, loc:'Austin, TX',        edu:'Vocational Diploma',     about:'2 years working in related field. Looking to formalise apprenticeship.',               resume:'zara_resume.pdf',    applied:'PowerBuild Inc',    visible:true,  status:'accepted' },
];

const MARKETPLACE = [
    { id:1,  name:'Professional Power Drill — Dewalt 20V', cat:'tools',     seller:'John Smith',    price:'$159.50',   cond:'Used',        loc:'San Francisco, CA', listed:'10 Jun 2026', status:'active',   emoji:'🔧' },
    { id:2,  name:'Scaffolding Frame Set 20ft',             cat:'equipment', seller:'BuildMax Ltd',  price:'$620.00',   cond:'Used',        loc:'Houston, TX',       listed:'09 Jun 2026', status:'active',   emoji:'🏗️' },
    { id:3,  name:'Safety Harness Kit Pro',                 cat:'equipment', seller:'SafetyFirst Co',price:'$89.00',    cond:'New',         loc:'Dallas, TX',        listed:'08 Jun 2026', status:'active',   emoji:'🦺' },
    { id:4,  name:'Roofing Shingles — 100 Bundles',         cat:'materials', seller:'TopCover Co',   price:'$2,400.00', cond:'New',         loc:'Austin, TX',        listed:'07 Jun 2026', status:'active',   emoji:'🏠' },
    { id:5,  name:'Circular Saw 7.25in Makita',             cat:'tools',     seller:'ToolMart Inc',  price:'$119.00',   cond:'Refurbished', loc:'Seattle, WA',       listed:'06 Jun 2026', status:'active',   emoji:'⚙️' },
    { id:6,  name:'Concrete Mixer 9 cu ft',                 cat:'equipment', seller:'SolidBase Inc', price:'$890.00',   cond:'Used',        loc:'Denver, CO',        listed:'05 Jun 2026', status:'inactive', emoji:'🔩' },
    { id:7,  name:'PVC Conduit 100ft Rolls x10',            cat:'materials', seller:'ElectroPro',    price:'$380.00',   cond:'New',         loc:'Chicago, IL',       listed:'04 Jun 2026', status:'active',   emoji:'🔌' },
    { id:8,  name:'ABC Plumbing Company — For Sale',        cat:'companies', seller:'Mike Torres',   price:'$125,000',  cond:'N/A',         loc:'Las Vegas, NV',     listed:'03 Jun 2026', status:'active',   emoji:'🏢' },
    { id:9,  name:'Hard Hat ANSI Class E Pack 12',          cat:'equipment', seller:'SafetyFirst Co',price:'$168.00',   cond:'New',         loc:'Phoenix, AZ',       listed:'02 Jun 2026', status:'active',   emoji:'⛑️' },
    { id:10, name:'Laser Level Self-Levelling',             cat:'tools',     seller:'ToolMart Inc',  price:'$210.00',   cond:'New',         loc:'Portland, OR',      listed:'01 Jun 2026', status:'active',   emoji:'📐' },
];

const REVIEWS = [
    { reviewer:'Sahil Mehta',    reviewed:'James Holt',   type:'contractor',    overall:5, comm:5, qual:5, payment:5, workenv:5, recommend:true,  comment:'Excellent work. Very professional. Completed on time.',   date:'10 Jun 2026' },
    { reviewer:'ABC Construction',reviewed:'Maria Lopez', type:'labour',        overall:4, comm:4, qual:4, payment:4, workenv:4, recommend:true,  comment:'Good worker, showed up on time and followed instructions.',date:'09 Jun 2026' },
    { reviewer:'BuildRight LLC', reviewed:'Derek Owens',  type:'subcontractor', overall:3, comm:3, qual:3, payment:3, workenv:3, recommend:false, comment:'Work acceptable but communication could be better.',       date:'08 Jun 2026' },
    { reviewer:'Marcus Webb',    reviewed:'SkyHigh Ltd',  type:'contractor',    overall:2, comm:2, qual:2, payment:2, workenv:2, recommend:false, comment:'Not happy with the experience. Management disorganised.',  date:'07 Jun 2026' },
    { reviewer:'Chen Liu',       reviewed:'CoolAir Sys',  type:'subcontractor', overall:4, comm:4, qual:5, payment:4, workenv:4, recommend:true,  comment:'Good project, fair pay and easy team.',                   date:'06 Jun 2026' },
    { reviewer:'Priya Sharma',   reviewed:'BuildSafe Inc',type:'contractor',    overall:5, comm:5, qual:5, payment:5, workenv:5, recommend:true,  comment:'Best experience. Safety training was excellent.',         date:'05 Jun 2026' },
    { reviewer:'Aisha Brown',    reviewed:'RusCon Co',    type:'contractor',    overall:5, comm:5, qual:5, payment:5, workenv:5, recommend:true,  comment:'Amazing apprenticeship program. Learned so much.',        date:'04 Jun 2026' },
    { reviewer:'Jake Morrison',  reviewed:'PowerBuild Inc',type:'subcontractor',overall:4, comm:4, qual:4, payment:5, workenv:4, recommend:true,  comment:'Great company. Pay was always on time.',                  date:'03 Jun 2026' },
];

const SUBS = [
    { user:'James Holt',   type:'contractor',    plan:'Contractor', amount:'$59.99', billing:'Monthly', started:'01 Jan 2026', next:'01 Jul 2026', status:'active'    },
    { user:'Emily Watson', type:'apprentice',    plan:'Apprentice', amount:'$9.99',  billing:'Monthly', started:'15 Mar 2026', next:'15 Jul 2026', status:'active'    },
    { user:'Sahil Mehta',  type:'contractor',    plan:'Contractor', amount:'$59.99', billing:'Monthly', started:'01 Feb 2026', next:'01 Jul 2026', status:'active'    },
    { user:'Maria Lopez',  type:'labour',        plan:'Labourer',   amount:'$19.99', billing:'Monthly', started:'10 Apr 2026', next:'10 Jul 2026', status:'active'    },
    { user:'Derek Owens',  type:'subcontractor', plan:'Contractor', amount:'$59.99', billing:'Monthly', started:'01 Mar 2026', next:'01 Jul 2026', status:'active'    },
    { user:'John Doe',     type:'contractor',    plan:'Contractor', amount:'$59.99', billing:'Monthly', started:'01 Dec 2025', next:'—',           status:'cancelled' },
    { user:'Priya Sharma', type:'apprentice',    plan:'Apprentice', amount:'Free',   billing:'Trial',   started:'01 May 2026', next:'01 Aug 2026', status:'trial'     },
    { user:'Marcus Webb',  type:'labour',        plan:'Labourer',   amount:'$19.99', billing:'Monthly', started:'15 Feb 2026', next:'—',           status:'expired'   },
    { user:'Chen Liu',     type:'subcontractor', plan:'Contractor', amount:'$59.99', billing:'Monthly', started:'01 Jan 2026', next:'01 Jul 2026', status:'active'    },
    { user:'Aisha Brown',  type:'apprentice',    plan:'Apprentice', amount:'Free',   billing:'Trial',   started:'10 Jun 2026', next:'10 Sep 2026', status:'trial'     },
];

// ── Render functions ──────────────────────────────────────────────────────────

function renderUsers(data) {
    const sb = { active: 'bg', inactive: 'bgr', suspended: 'br' };
    document.getElementById('uTbody').innerHTML = data.map(u => `
        <tr>
            <td><input type="checkbox"></td>
            <td>
                <div class="uc">
                    <div class="ua" style="background:${TC[u.type]}">${ini(u.name)}</div>
                    <div><div class="un">${u.name}</div><div class="ue">${u.email}</div></div>
                </div>
            </td>
            <td><span class="bdg ${TB[u.type]}">${TL[u.type]}</span></td>
            <td class="text-grey">${u.city}, ${u.state}</td>
            <td style="font-size:12px;max-width:120px">${u.trade}</td>
            <td class="text-grey">${u.exp}</td>
            <td style="text-align:center"><span class="bdg ${u.insured === 'Yes' ? 'bg' : 'br'}">${u.insured}</span></td>
            <td style="text-align:center"><span class="bdg ${u.bg === 'Cleared' ? 'bg' : u.bg === 'Pending' ? 'ba' : 'br'}">${u.bg}</span></td>
            <td style="font-size:12px">${u.plan}</td>
            <td><span class="bdg ${sb[u.status]}">${u.status}</span></td>
            <td>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-ol btn-xs" onclick="viewUser(${u.id})">View</button>
                    <button class="btn btn-dn btn-xs" onclick="openMov('Delete User','Delete &quot;${u.name}&quot;? Cannot be undone.',()=>toast('${u.name} deleted.','err'))">Del</button>
                </div>
            </td>
        </tr>`).join('');
}

function renderJobs(data) {
    const sb = { open: 'bg', pending: 'ba', completed: 'bt', closed: 'bgr' };
    document.getElementById('jTbody').innerHTML = data.map(j => `
        <tr>
            <td><input type="checkbox"></td>
            <td><div class="un">${j.title}</div><div class="ue">${j.company} ★${j.compRating}</div></td>
            <td class="text-grey">${j.company}</td>
            <td><span class="bdg ${TB[j.type] || 'bgr'}">${TL[j.type] || j.type}</span></td>
            <td style="max-width:140px">${j.skills.map(s => '<span class="skill-chip">' + s + '</span>').join('')}</td>
            <td style="font-size:13px;font-weight:700;color:var(--navy)">$${j.pay}/hr</td>
            <td class="text-grey">${j.loc}</td>
            <td class="text-grey">${j.duration}</td>
            <td class="text-grey">${j.start}</td>
            <td style="text-align:center"><span class="bdg ${j.featured ? 'bo' : 'bgr'}">${j.featured ? 'Featured' : 'No'}</span></td>
            <td><span class="bdg ${sb[j.status]}">${j.status}</span></td>
            <td>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-ol btn-xs" onclick="viewJob(${j.id})">View</button>
                    <button class="btn btn-dn btn-xs" onclick="openMov('Delete Job','Delete &quot;${j.title}&quot;?',()=>toast('Job deleted.','err'))">Del</button>
                </div>
            </td>
        </tr>`).join('');
}

function renderApps(data) {
    const sb = { open: 'bg', closed: 'br' };
    document.getElementById('aOppTbody').innerHTML = data.map(a => `
        <tr>
            <td><input type="checkbox"></td>
            <td><div class="uc"><div class="ua" style="background:var(--purple)">${a.logo}</div><div class="un">${a.company}</div></div></td>
            <td style="font-size:12px">${a.trade}</td>
            <td style="font-size:13px;font-weight:700;color:var(--navy)">${a.comp}</td>
            <td class="text-grey">${a.loc}</td>
            <td class="text-grey">${a.start}</td>
            <td class="text-grey">${a.duration}</td>
            <td style="text-align:center"><span class="bdg bn">${a.questions} questions</span></td>
            <td style="text-align:center"><span class="bdg bp">${a.applicants} applied</span></td>
            <td style="font-size:11.5px">${a.pricing}</td>
            <td><span class="bdg ${sb[a.status]}">${a.status}</span></td>
            <td>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-ol btn-xs" onclick="viewApp(${a.id})">View</button>
                    <button class="btn btn-dn btn-xs" onclick="openMov('Delete Opportunity','Delete listing?',()=>toast('Deleted.','err'))">Del</button>
                </div>
            </td>
        </tr>`).join('');
}

function renderApplicants(data) {
    const sb = { pending: 'ba', accepted: 'bg', rejected: 'br' };
    document.getElementById('aAppTbody').innerHTML = data.map(a => `
        <tr>
            <td><input type="checkbox"></td>
            <td><div class="uc"><div class="ua" style="background:var(--purple)">${ini(a.name)}</div><div><div class="un">${a.name}</div><div class="ue">${a.loc}</div></div></div></td>
            <td style="font-size:12.5px">${a.trade}</td>
            <td class="text-grey">${a.age}</td>
            <td class="text-grey">${a.loc}</td>
            <td class="text-grey">${a.edu}</td>
            <td style="font-size:12px;color:var(--grey);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${a.about}">${a.about}</td>
            <td style="text-align:center"><span class="bdg ${a.resume ? 'bg' : 'bgr'}">${a.resume ? 'Uploaded' : 'None'}</span></td>
            <td style="font-size:12.5px">${a.applied}</td>
            <td style="text-align:center"><span class="bdg ${a.visible ? 'bg' : 'bgr'}">${a.visible ? 'Visible' : 'Hidden'}</span></td>
            <td><span class="bdg ${sb[a.status]}">${a.status}</span></td>
            <td>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-ol btn-xs" onclick="viewApplicant(${a.id})">View</button>
                    <button class="btn btn-or btn-xs" onclick="toast('Message sent.','ok')">Msg</button>
                </div>
            </td>
        </tr>`).join('');
}

function renderMarket(data) {
    const cb = { tools: 'bn', equipment: 'bo', materials: 'bt', companies: 'bp' };
    const cl = { tools: 'Tools', equipment: 'Equipment', materials: 'Materials', companies: 'Company' };
    document.getElementById('mTbody').innerHTML = data.map(p => `
        <tr>
            <td><input type="checkbox"></td>
            <td><div class="uc"><div style="font-size:20px;width:32px;text-align:center">${p.emoji}</div><div class="un" style="font-size:13px">${p.name}</div></div></td>
            <td><span class="bdg ${cb[p.cat] || 'bgr'}">${cl[p.cat] || p.cat}</span></td>
            <td class="text-grey">${p.seller}</td>
            <td style="font-size:13px;font-weight:700;color:var(--navy)">${p.price}</td>
            <td class="text-grey">${p.cond}</td>
            <td class="text-grey">${p.loc}</td>
            <td class="text-grey">${p.listed}</td>
            <td><span class="bdg ${p.status === 'active' ? 'bg' : 'bgr'}">${p.status}</span></td>
            <td>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-ol btn-xs" onclick="viewListing(${p.id})">View</button>
                    <button class="btn btn-dn btn-xs" onclick="openMov('Remove Listing','Remove this listing?',()=>toast('Removed.','err'))">Remove</button>
                </div>
            </td>
        </tr>`).join('');
}

function renderReviews() {
    document.getElementById('rTbody').innerHTML = REVIEWS.map(r => `
        <tr>
            <td style="font-size:12.5px;font-weight:600">${r.reviewer}</td>
            <td class="text-grey">${r.reviewed}<br><span class="bdg ${TB[r.type] || 'bgr'}" style="font-size:10px">${TL[r.type] || r.type}</span></td>
            <td><span style="color:var(--amber)">${stars(r.overall)}</span> <span style="font-size:11.5px;font-weight:600">${r.overall}.0</span></td>
            <td style="text-align:center;font-size:12px;font-weight:600">${r.comm}/5</td>
            <td style="text-align:center;font-size:12px;font-weight:600">${r.qual}/5</td>
            <td style="text-align:center;font-size:12px;font-weight:600">${r.payment}/5</td>
            <td style="text-align:center;font-size:12px;font-weight:600">${r.workenv}/5</td>
            <td style="text-align:center"><span class="bdg ${r.recommend ? 'bg' : 'br'}">${r.recommend ? 'Yes' : 'No'}</span></td>
            <td style="font-size:12px;color:var(--grey);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.comment}</td>
            <td style="font-size:12px;color:var(--grey-l)">${r.date}</td>
            <td>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-ol btn-xs" onclick="toast('Review expanded.','inf')">View</button>
                    <button class="btn btn-dn btn-xs" onclick="openMov('Remove Review','Remove this review?',()=>toast('Removed.','err'))">Remove</button>
                </div>
            </td>
        </tr>`).join('');
}

function renderSubs() {
    const sb = { active: 'bg', cancelled: 'br', trial: 'ba', expired: 'bgr' };
    const pb = { Contractor: 'bo', Labourer: 'bn', Apprentice: 'bp' };
    document.getElementById('sTbody').innerHTML = SUBS.map(s => `
        <tr>
            <td style="font-size:13px;font-weight:600">${s.user}</td>
            <td><span class="bdg ${TB[s.type] || 'bgr'}">${TL[s.type] || s.type}</span></td>
            <td><span class="bdg ${pb[s.plan] || 'bgr'}">${s.plan}</span></td>
            <td style="font-size:13px;font-weight:700;color:var(--navy)">${s.amount}</td>
            <td class="text-grey">${s.billing}</td>
            <td class="text-grey">${s.started}</td>
            <td class="text-grey">${s.next}</td>
            <td><span class="bdg ${sb[s.status]}">${s.status}</span></td>
            <td>
                <div style="display:flex;gap:4px">
                    <button class="btn btn-ol btn-xs" onclick="toast('Viewing subscription...','inf')">View</button>
                    <button class="btn btn-dn btn-xs" onclick="toast('Subscription cancelled.','err')">Cancel</button>
                </div>
            </td>
        </tr>`).join('');
}

// ── Detail panels ─────────────────────────────────────────────────────────────

function viewUser(id) {
    const u = USERS.find(x => x.id === id);
    if (!u) return;
    document.getElementById('dpT').textContent = 'User Profile';
    document.getElementById('dpB').innerHTML =
        '<div class="dpav-w">' +
        '<div class="dpav" style="background:' + TC[u.type] + '">' + ini(u.name) + '</div>' +
        '<div class="dpn">' + u.name + '</div>' +
        '<div class="dpr">' + TL[u.type] + ' &mdash; ' + u.city + ', ' + u.state + '</div>' +
        '<div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;justify-content:center">' +
        '<span class="bdg ' + (u.status === 'active' ? 'bg' : u.status === 'suspended' ? 'br' : 'bgr') + '">' + u.status + '</span>' +
        '<span class="bdg ' + TB[u.type] + '">' + TL[u.type] + '</span>' +
        (u.insured === 'Yes' ? '<span class="bdg bg">Insured</span>' : '<span class="bdg br">Not Insured</span>') +
        '</div></div>' +
        '<div class="dp-sec">Account Info</div>' +
        '<div class="dp-row"><span class="dpk">Email</span><span class="dpv">' + u.email + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Location</span><span class="dpv">' + u.city + ', ' + u.state + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Gender</span><span class="dpv">' + u.gender + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Plan</span><span class="dpv">' + u.plan + '</span></div>' +
        '<div class="dp-sec">Trade Info</div>' +
        '<div class="dp-row"><span class="dpk">Trade</span><span class="dpv">' + u.trade + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Experience</span><span class="dpv">' + u.exp + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Available Now</span><span class="dpv">' + (u.available ? 'Yes' : 'No') + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Seeking Apprenticeship</span><span class="dpv">' + (u.apprentice ? 'Yes' : 'No') + '</span></div>' +
        '<div class="dp-sec">Background &amp; Social</div>' +
        '<div class="dp-row"><span class="dpk">Background Check</span><span class="dpv"><span class="bdg ' + (u.bg === 'Cleared' ? 'bg' : u.bg === 'Pending' ? 'ba' : 'br') + '">' + u.bg + '</span></span></div>' +
        '<div class="dp-row"><span class="dpk">Followers</span><span class="dpv">' + u.followers + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Following</span><span class="dpv">' + u.following + '</span></div>' +
        '<div class="dp-sec">Admin Actions</div>' +
        '<div class="dp-acts">' +
        (u.status === 'active' ? '<button class="btn btn-am btn-sm" style="flex:1" onclick="toast(\'' + u.name + ' suspended.\',\'err\');closeDp()">Suspend</button>' : '') +
        (u.status === 'active' ? '<button class="btn btn-ol btn-sm" style="flex:1" onclick="toast(\'Set inactive.\',\'inf\');closeDp()">Set Inactive</button>' : '') +
        (u.status !== 'active' ? '<button class="btn btn-pr btn-sm" style="flex:1" onclick="toast(\'Reactivated.\',\'ok\');closeDp()">Reactivate</button>' : '') +
        '<button class="btn btn-dn btn-sm" onclick="openMov(\'Delete User\',\'Permanently delete ' + u.name + '?\',()=>{toast(\'' + u.name + ' deleted.\',\'err\');closeDp()})">Delete</button>' +
        '</div>';
    document.getElementById('dov').classList.add('open');
}

function viewJob(id) {
    const j = JOBS.find(x => x.id === id);
    if (!j) return;
    const sb = { open: 'bg', pending: 'ba', completed: 'bt', closed: 'bgr' };
    document.getElementById('dpT').textContent = 'Job Details';
    document.getElementById('dpB').innerHTML =
        '<div class="dpav-w">' +
        '<div class="dpav" style="background:' + (TC[j.type] || '#1B3D6F') + ';border-radius:var(--r);font-size:26px">&#128188;</div>' +
        '<div class="dpn">' + j.title + '</div>' +
        '<div class="dpr">' + j.company + ' &mdash; &#9733;' + j.compRating + '</div>' +
        '<span class="bdg ' + sb[j.status] + '" style="margin-top:8px">' + j.status + '</span>' +
        '</div>' +
        '<div class="dp-sec">Job Details</div>' +
        '<div class="dp-row"><span class="dpk">Type Required</span><span class="dpv">' + (TL[j.type] || j.type) + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Skills Needed</span><span class="dpv">' + j.skills.join(', ') + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Pay Rate</span><span class="dpv" style="color:var(--navy);font-weight:700">$' + j.pay + '/hr</span></div>' +
        '<div class="dp-row"><span class="dpk">Location</span><span class="dpv">' + j.loc + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Start Date</span><span class="dpv">' + j.start + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Duration</span><span class="dpv">' + j.duration + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Available Today</span><span class="dpv">' + (j.available ? 'Yes' : 'No') + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Featured Posting</span><span class="dpv">' + (j.featured ? 'Yes &mdash; $10 fee' : 'No') + '</span></div>' +
        '<div class="dp-sec">Description</div>' +
        '<div style="font-size:12.5px;color:var(--grey);line-height:1.6;padding:8px 0">' + j.desc + '</div>' +
        '<div class="dp-acts"><button class="btn btn-dn" style="flex:1" onclick="openMov(\'Delete Job\',\'Delete &quot;' + j.title + '&quot;?\',()=>{toast(\'Job deleted.\',\'err\');closeDp()})">Delete Job</button></div>';
    document.getElementById('dov').classList.add('open');
}

function viewApp(id) {
    const a = APPRENTICESHIPS.find(x => x.id === id);
    if (!a) return;
    document.getElementById('dpT').textContent = 'Apprenticeship Opportunity';
    document.getElementById('dpB').innerHTML =
        '<div class="dpav-w">' +
        '<div class="dpav" style="background:var(--purple);border-radius:var(--r);font-size:26px">&#127891;</div>' +
        '<div class="dpn">' + a.company + '</div>' +
        '<div class="dpr">' + a.trade + '</div>' +
        '<span class="bdg ' + (a.status === 'open' ? 'bg' : 'br') + '" style="margin-top:8px">' + a.status + '</span>' +
        '</div>' +
        '<div class="dp-sec">Opportunity Details</div>' +
        '<div class="dp-row"><span class="dpk">Compensation</span><span class="dpv" style="color:var(--navy);font-weight:700">' + a.comp + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Location</span><span class="dpv">' + a.loc + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Start Date</span><span class="dpv">' + a.start + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Duration</span><span class="dpv">' + a.duration + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Applicants</span><span class="dpv">' + a.applicants + ' applied</span></div>' +
        '<div class="dp-row"><span class="dpk">Screening Questions</span><span class="dpv">' + a.questions + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Platform Pricing</span><span class="dpv">' + a.pricing + '</span></div>' +
        '<div class="dp-sec">About the Company</div>' +
        '<div style="font-size:12.5px;color:var(--grey);line-height:1.6;padding:8px 0">' + a.about + '</div>' +
        '<div class="dp-sec">Requirements</div>' +
        '<div style="font-size:12.5px;color:var(--grey);line-height:1.6;padding:8px 0">' + a.requirements + '</div>' +
        '<div class="dp-acts">' +
        '<button class="btn btn-pr" style="flex:1" onclick="closeDp();document.querySelector(\'[data-at=app]\').click()">View Applicants</button>' +
        '<button class="btn btn-dn" onclick="openMov(\'Delete Opportunity\',\'Delete listing?\',()=>{toast(\'Deleted.\',\'err\');closeDp()})">Delete</button>' +
        '</div>';
    document.getElementById('dov').classList.add('open');
}

function viewApplicant(id) {
    const a = APPLICANTS.find(x => x.id === id);
    if (!a) return;
    const sb = { pending: 'ba', accepted: 'bg', rejected: 'br' };
    document.getElementById('dpT').textContent = 'Applicant Profile';
    document.getElementById('dpB').innerHTML =
        '<div class="dpav-w">' +
        '<div class="dpav" style="background:var(--purple)">' + ini(a.name) + '</div>' +
        '<div class="dpn">' + a.name + '</div>' +
        '<div class="dpr">Apprentice &mdash; ' + a.loc + '</div>' +
        '<span class="bdg ' + sb[a.status] + '" style="margin-top:8px">' + a.status + '</span>' +
        '</div>' +
        '<div class="dp-sec">Personal Info</div>' +
        '<div class="dp-row"><span class="dpk">Age</span><span class="dpv">' + a.age + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Location</span><span class="dpv">' + a.loc + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Education</span><span class="dpv">' + a.edu + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Trade Interest</span><span class="dpv">' + a.trade + '</span></div>' +
        '<div class="dp-sec">Application</div>' +
        '<div class="dp-row"><span class="dpk">Applied For</span><span class="dpv">' + a.applied + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Resume</span><span class="dpv"><span class="bdg ' + (a.resume ? 'bg' : 'bgr') + '">' + (a.resume || 'Not uploaded') + '</span></span></div>' +
        '<div class="dp-row"><span class="dpk">Profile Visible</span><span class="dpv"><span class="bdg ' + (a.visible ? 'bg' : 'bgr') + '">' + (a.visible ? 'Visible to Contractors' : 'Hidden') + '</span></span></div>' +
        '<div class="dp-sec">About Me</div>' +
        '<div style="font-size:12.5px;color:var(--grey);line-height:1.6;padding:8px 0">' + a.about + '</div>' +
        '<div class="dp-acts">' +
        '<button class="btn btn-pr" style="flex:1" onclick="toast(\'Message sent.\',\'ok\');closeDp()">Message</button>' +
        '<button class="btn btn-dn" onclick="openMov(\'Remove Applicant\',\'Remove from platform?\',()=>{toast(\'Removed.\',\'err\');closeDp()})">Remove</button>' +
        '</div>';
    document.getElementById('dov').classList.add('open');
}

function viewListing(id) {
    const p = MARKETPLACE.find(x => x.id === id);
    if (!p) return;
    document.getElementById('dpT').textContent = 'Listing Details';
    document.getElementById('dpB').innerHTML =
        '<div class="dpav-w">' +
        '<div style="font-size:48px;margin-bottom:10px">' + p.emoji + '</div>' +
        '<div class="dpn">' + p.name + '</div>' +
        '<div class="dpr">Listed by ' + p.seller + '</div>' +
        '<span class="bdg ' + (p.status === 'active' ? 'bg' : 'bgr') + '" style="margin-top:8px">' + p.status + '</span>' +
        '</div>' +
        '<div class="dp-sec">Listing Details</div>' +
        '<div class="dp-row"><span class="dpk">Category</span><span class="dpv">' + p.cat + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Price</span><span class="dpv" style="color:var(--navy);font-weight:700">' + p.price + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Condition</span><span class="dpv">' + p.cond + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Location</span><span class="dpv">' + p.loc + '</span></div>' +
        '<div class="dp-row"><span class="dpk">Listed Date</span><span class="dpv">' + p.listed + '</span></div>' +
        '<div class="dp-acts">' +
        '<button class="btn btn-am btn-sm" style="flex:1" onclick="toast(\'Listing flagged.\',\'inf\');closeDp()">Flag</button>' +
        '<button class="btn btn-dn" onclick="openMov(\'Remove Listing\',\'Remove this listing?\',()=>{toast(\'Removed.\',\'err\');closeDp()})">Remove</button>' +
        '</div>';
    document.getElementById('dov').classList.add('open');
}

function closeDp() { document.getElementById('dov').classList.remove('open'); }
document.getElementById('dov').addEventListener('click', e => { if (e.target === e.currentTarget) closeDp(); });

// ── Modal ─────────────────────────────────────────────────────────────────────
let _cb = null;
function openMov(title, body, cb) {
    document.getElementById('mH').textContent = title;
    document.getElementById('mB').innerHTML = body;
    _cb = cb;
    document.getElementById('mov').classList.add('open');
}
function closeMov() { document.getElementById('mov').classList.remove('open'); _cb = null; }
document.getElementById('mConf').addEventListener('click', () => { if (_cb) _cb(); closeMov(); });
document.getElementById('mov').addEventListener('click', e => { if (e.target === e.currentTarget) closeMov(); });

// ── Sidebar & navigation ──────────────────────────────────────────────────────
const modNames = {
    dashboard:     'Dashboard',
    users:         'Users',
    jobs:          'Jobs',
    apprentice:    'Apprenticeships',
    marketplace:   'Marketplace',
    reviews:       'Reviews & Ratings',
    subscriptions: 'Subscriptions',
};

document.querySelectorAll('.sb-item[data-mod]').forEach(item => {
    item.addEventListener('click', () => {
        const mod = item.dataset.mod;
        document.querySelectorAll('.sb-item').forEach(n => n.classList.remove('act'));
        item.classList.add('act');
        document.querySelectorAll('.mod').forEach(m => m.classList.remove('act'));
        document.getElementById('mod-' + mod).classList.add('act');
        document.getElementById('tbT').textContent = modNames[mod] || 'Dashboard';
    });
});

document.getElementById('sbTog').addEventListener('click', () => {
    document.getElementById('sb').classList.toggle('col');
});

// ── User filters ──────────────────────────────────────────────────────────────
let activeUT = 'all';

document.getElementById('uTabs').addEventListener('click', e => {
    const btn = e.target.closest('.tab');
    if (!btn || !btn.dataset.ut) return;
    document.querySelectorAll('#uTabs .tab').forEach(b => b.classList.remove('act'));
    btn.classList.add('act');
    activeUT = btn.dataset.ut;
    renderUsers(filtU());
});

function filtU() {
    const s  = (document.getElementById('uSrch')?.value || '').toLowerCase();
    const st = document.getElementById('uStat')?.value || 'all';
    return USERS.filter(u =>
        (activeUT === 'all' || u.type === activeUT) &&
        (!s || u.name.toLowerCase().includes(s) || u.email.toLowerCase().includes(s) || u.city.toLowerCase().includes(s)) &&
        (st === 'all' || u.status === st)
    );
}

['uSrch', 'uStat', 'uSub', 'uInsured'].forEach(id => {
    document.getElementById(id)?.addEventListener('input',  () => renderUsers(filtU()));
    document.getElementById(id)?.addEventListener('change', () => renderUsers(filtU()));
});

// ── Job filters ───────────────────────────────────────────────────────────────
function filtJ() {
    const s  = (document.getElementById('jSrch')?.value || '').toLowerCase();
    const st = document.getElementById('jStat')?.value || 'all';
    const ty = document.getElementById('jType')?.value || 'all';
    return JOBS.filter(j =>
        (!s || j.title.toLowerCase().includes(s) || j.company.toLowerCase().includes(s) || j.loc.toLowerCase().includes(s)) &&
        (st === 'all' || j.status === st) &&
        (ty === 'all' || j.type === ty)
    );
}

['jSrch', 'jStat', 'jType', 'jAvail'].forEach(id => {
    document.getElementById(id)?.addEventListener('input',  () => renderJobs(filtJ()));
    document.getElementById(id)?.addEventListener('change', () => renderJobs(filtJ()));
});

// ── Apprenticeship tabs ───────────────────────────────────────────────────────
document.getElementById('aTabs').addEventListener('click', e => {
    const btn = e.target.closest('.tab');
    if (!btn || !btn.dataset.at) return;
    document.querySelectorAll('#aTabs .tab').forEach(b => b.classList.remove('act'));
    btn.classList.add('act');
    const at = btn.dataset.at;
    document.getElementById('aOppDiv').style.display  = at === 'opp' ? 'block' : 'none';
    document.getElementById('aAppDiv').style.display  = at === 'app' ? 'block' : 'none';
    document.getElementById('aPagI').textContent = at === 'opp' ? 'Showing 1-5 of 31' : 'Showing 1-5 of 87';
});

// ── Marketplace filters ───────────────────────────────────────────────────────
let activeMT = 'all';

document.getElementById('mTabs').addEventListener('click', e => {
    const btn = e.target.closest('.tab');
    if (!btn || !btn.dataset.mt) return;
    document.querySelectorAll('#mTabs .tab').forEach(b => b.classList.remove('act'));
    btn.classList.add('act');
    activeMT = btn.dataset.mt;
    renderMarket(filtM());
});

function filtM() {
    const s = (document.getElementById('mSrch')?.value || '').toLowerCase();
    const c = document.getElementById('mCond')?.value || 'all';
    return MARKETPLACE.filter(p =>
        (activeMT === 'all' || p.cat === activeMT) &&
        (!s || p.name.toLowerCase().includes(s) || p.seller.toLowerCase().includes(s)) &&
        (c === 'all' || p.cond.toLowerCase() === c)
    );
}

['mSrch', 'mCond'].forEach(id => {
    document.getElementById(id)?.addEventListener('input',  () => renderMarket(filtM()));
    document.getElementById(id)?.addEventListener('change', () => renderMarket(filtM()));
});

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderUsers(USERS);
    renderJobs(JOBS);
    renderApps(APPRENTICESHIPS);
    renderApplicants(APPLICANTS);
    renderMarket(MARKETPLACE);
    renderReviews();
    renderSubs();
});
