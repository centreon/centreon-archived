var ed=Object.defineProperty;var td=(n,e,o)=>e in n?ed(n,e,{enumerable:!0,configurable:!0,writable:!0,value:o}):n[e]=o;var h=(n,e,o)=>(td(n,typeof e!="symbol"?e+"":e,o),o);import{c as S,r as c,m as Z,T as er,I as Je,i as Fn,C as tr,a as St,b as od,n as id,e as q,d as or,A as ad,f as mo,p as ir,g as ar,h as Ge,M as rd,j as cd,k as sd,u as ld,l as pd,o as rr,q as dd,s as md,t as de,v as bd,w as Xe,x as bo,y as xd,z as fd,F as gd,B as yd,S as ud,D as Ct,L as hd,E as cr,G as sr,H as wd,J as Tt,K as _d,P as kd,N as xo,O as si,Q as Ed,R as li,U as lr,V as pr,W as ee,X as Ad,Y as pi,Z as vd,_ as dr,$ as mr,a0 as zd,a1 as Sd,a2 as Cd,a3 as Td,a4 as Id,a5 as Nd,a6 as It,a7 as Rd,a8 as Gd,a9 as Dd,aa as Hd,ab as di,ac as Fd,ad as Pd,ae as Ld,af as $d,ag as br,ah as Md,ai as fo,aj as Od,ak as Bd,al as Ud,am as Vd,an as jd,ao as qd,ap as Wd,aq as Kd,ar as Yd,as as Qd,at as Jd,au as Xd,av as xr,aw as fr,ax as gr,ay as yr,az as ur,aA as Zd,aB as nm,aC as em,aD as tm,aE as om,aF as im,aG as am,aH as rm,aI as cm,aJ as sm,aK as lm,aL as pm,aM as hr,aN as wr,aO as dm,aP as mm,aQ as bm,aR as xm,aS as fm,aT as gm,aU as Ze,aV as ym,aW as um,aX as hm,aY as wm,aZ as nt,a_ as mi,a$ as _r,b0 as bi,b1 as De,b2 as he,b3 as _m,b4 as km,b5 as Em,b6 as Am,b7 as vm,b8 as zm,b9 as Sm,ba as Cm,bb as Tm,bc as Im,bd as Nm,be as Rm,bf as Gm,bg as Dm,bh as P,bi as t,bj as Hm,bk as jn,bl as qn,bm as $n,bn as j,bo as _n,bp as xi,bq as go,br as me,bs as tn,bt as M,bu as gn,bv as V,bw as kr,bx as te,by as dn,bz as D,bA as Yn,bB as Fm,bC as E,bD as kn,bE as Nt,bF as X,bG as Er,bH as Ar,bI as we,bJ as fi,bK as Pm,bL as Lm,bM as vr,bN as yo,bO as uo,bP as mn,bQ as be,bR as Rt,bS as ln,bT as gi,bU as yi,bV as zr,bW as et,bX as ui,bY as Gt,bZ as oe,b_ as pn,b$ as $m,c0 as Sr,c1 as W,c2 as K,c3 as Mm,c4 as hi,c5 as an,c6 as Mn,c7 as wi,c8 as Om,c9 as Dt,ca as L,cb as Ae,cc as _i,cd as ho,ce as Wn,cf as Bm,cg as Um,ch as Vm,ci as jm,cj as qm,ck as Cr,cl as Tr,cm as Ir,cn as xn,co as Wm,cp as ve,cq as rn,cr as zn,cs as tt,ct as Ht,cu as Nr,cv as Km,cw as ot,cx as Ym,cy as He,cz as Qm,cA as Jm,cB as Xm,cC as Zm,cD as Rr,cE as Ft,cF as Gr,cG as nb,cH as Dr,cI as _,cJ as eb,cK as ki,cL as Pt,cM as Lt,cN as Hr,cO as wo,cP as Fr,cQ as tb,cR as ob,cS as ib,cT as ab,cU as Ei,cV as rb,cW as Ai,cX as cb,cY as sb,cZ as vi,c_ as _o,c$ as lb,d0 as _e,d1 as ko,d2 as it,d3 as pb,d4 as db,d5 as mb,d6 as zi,d7 as bb,d8 as xb,d9 as fb,da as Si,db as Ci,dc as Pr,dd as gb,de as yb,df as ub,dg as hb,dh as wb,di as _b,dj as Eo,dk as Lr,dl as $r,dm as kb,dn as Eb,dp as $t,dq as Ab,dr as vb,ds as Mr,dt as zb,du as Sb,dv as Cb,dw as Tb,dx as Ib,dy as Nb,dz as Ti,dA as Rb,dB as Gb,dC as Ao,dD as Or,dE as Db,dF as Hb,dG as Fb,dH as Br,dI as Ii,dJ as Ni,dK as Pb,dL as Lb,dM as $b,dN as Mb,dO as Ob,dP as Bb,dQ as Ub,dR as Vb,dS as Ri,dT as jb,dU as Ur,dV as qb,dW as Gi,dX as Vr,dY as jr,dZ as Mt,d_ as qr,d$ as Wb,e0 as Wr,e1 as Kb,e2 as Yb,e3 as Qb,e4 as Jb,e5 as Xb,e6 as Zb,e7 as nx,e8 as vo,e9 as zo,ea as ex,eb as tx,ec as ox,ed as ix,ee as ax,ef as rx,eg as cx,eh as sx,ei as Kr,ej as lx,ek as px,el as dx,em as mx,en as bx,eo as xx,ep as fx,eq as gx,er as yx,es as ux,et as hx,eu as wx,ev as _x,ew as kx,ex as Ex,ey as Ax,ez as vx,eA as Yr,eB as at,eC as zx,eD as Sx,eE as Cx,eF as Tx,eG as Ix,eH as Nx,eI as Rx,eJ as Gx,eK as Dx,eL as Hx,eM as Fx,eN as Px,eO as Lx,eP as $x,eQ as Mx,eR as Ox,eS as Bx,eT as Ux}from"./vendor.a17cc540.js";var rt=`@charset "UTF-8";
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
@font-face {
  font-family: "icomoon";
  src: url("__VITE_ASSET__86322399__");
  src: url("__VITE_ASSET__86322399__") format("embedded-opentype"), url("__VITE_ASSET__434e2b1b__") format("truetype"), url("__VITE_ASSET__8b06532b__") format("woff"), url("__VITE_ASSET__9d46154f__") format("svg");
  font-weight: normal;
  font-style: normal;
}
.icon-action {
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  display: inline-block;
  font-size: 25px;
}
.icon-action-update:after {
  content: "\uE911";
  color: #dcdcdc;
}
.icon-action-update.white:after {
  color: #ffffff;
}
.icon-action-update.gray:after {
  color: #cdcdcd;
}
.icon-action-update.orange:after {
  color: #ff7200;
}
.icon-action-add:after {
  content: "\uE921";
  color: #dcdcdc;
}
.icon-action-add.white:after {
  color: #ffffff;
}
.icon-action-add.gray:after {
  color: #cdcdcd;
}
.icon-action-add.orange:after {
  color: #ff7200;
}
.icon-action-delete:after {
  content: "\uE910";
  color: #dcdcdc;
}
.icon-action-delete.white:after {
  color: #ffffff;
}
.icon-action-delete.gray:after {
  color: #cdcdcd;
}
.icon-action-delete.orange:after {
  color: #ff7200;
}
.icon-action-delete-white:after {
  content: "\uE910";
  color: #ffffff;
}
.icon-action-clock:after {
  content: "\uE914";
  color: #dcdcdc;
}
.icon-action-clock.white:after {
  color: #ffffff;
}
.icon-action-clock.gray:after {
  color: #cdcdcd;
}
.icon-action-clock.orange:after {
  color: #ff7200;
}
.icon-action-check:after {
  content: "\uE913";
  color: #dcdcdc;
}
.icon-action-check.white:after {
  color: #ffffff;
}
.icon-action-check.gray:after {
  color: #cdcdcd;
}
.icon-action-check.green:after {
  color: #43b02a;
}
.icon-action-check.orange:after {
  color: #ff7200;
}
.icon-action-warning:after {
  content: "\uE920";
  color: #dcdcdc;
}
.icon-action-warning.white:after {
  color: #ffffff;
}
.icon-action-warning.gray:after {
  color: #cdcdcd;
}
.icon-action-warning.red:after {
  color: #e00b3d;
}
.icon-action-warning.orange:after {
  color: #ff7200;
}
.icon-action-arrow-right:after {
  content: "\uE923";
  color: #dcdcdc;
}
.icon-action-arrow-right.white:after {
  color: #ffffff;
}
.icon-action-arrow-right.gray:after {
  color: #cdcdcd;
}
.icon-action-arrow-right.red:after {
  color: #e00b3d;
}
.icon-action-arrow-right.orange:after {
  color: #ff7200;
}
.icon-action-arrow-left:after {
  content: "\uE924";
  color: #dcdcdc;
}
.icon-action-arrow-left.white:after {
  color: #ffffff;
}
.icon-action-arrow-left.gray:after {
  color: #cdcdcd;
}
.icon-action-arrow-left.red:after {
  color: #e00b3d;
}
.icon-action-arrow-left.orange:after {
  color: #ff7200;
}
.icon-position-left, .icon-position-right, .icon-position-center {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
}
.icon-position-left {
  left: 5px;
}
.icon-position-right {
  right: 5px;
}
.icon-position-center {
  left: 50%;
  transform: translate(-50%, -50%);
}
.icon-position-reset {
  position: static;
  transform: none;
}
.icon-position-counter {
  position: absolute;
  transform: rotate(90deg);
  right: 1px;
  top: 3px;
  font-size: 20px;
  cursor: pointer;
}
.icon-action-custom {
  right: 0;
  z-index: 1;
  position: absolute;
  font-size: 22px;
}
.icon-action-custom:after {
  color: #7fb345;
}`;const Di=({iconActionType:n,iconColor:e,iconDirection:o,customStyle:i,iconReset:a,...r})=>{const s=S(rt["icon-action"],{[rt[`icon-action-${n}`]]:!0},rt[e||""],rt[o||""],rt[i||""],rt[a||""]);return c.createElement("span",{className:s,...r})};var Fe=`/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
/* Colors */
/* Fonts */
.container {
  padding: 20px;
}
.container-gray {
  background-color: #f9f9f9;
}
.container-gray .container__col-md-3,
.container-gray .container__col-md-4,
.container-gray .container__col-sm-6,
.container-gray .container__col-md-9 {
  margin: 0;
}
.container-blue {
  background-color: #29d1d4;
}
.container-red {
  background-color: #e00b3d;
}
.content-wrapper {
  padding: 12px 20px 0 20px;
  box-sizing: border-box;
  margin: 0 auto;
}
@media (max-width: 767px) {
  .content-wrapper {
    padding: 12px;
  }
}
.content-wrap {
  height: 100vh;
  display: flex;
  flex-direction: column;
}
.content-inner {
  flex: 1;
  overflow: auto;
}
.content-overflow {
  flex: 1;
  min-height: 0px;
}
/* Colors */
/* Fonts */
.m-0 {
  margin: 0 !important;
}
.mb-0 {
  margin-bottom: 0 !important;
}
.mb-1 {
  margin-bottom: 10px;
}
.mb-2 {
  margin-bottom: 20px;
}
.mr-2 {
  margin-right: 20px;
}
.mr-4 {
  margin-right: 40px;
}
.ml-1 {
  margin-left: 10px;
}
.ml-2 {
  margin-left: 20px;
}
.mt-03 {
  margin-top: 3px !important;
}
.mt-05 {
  margin-top: 5px !important;
}
.mt-1 {
  margin-top: 10px;
}
.mt-2 {
  margin-top: 20px;
}
.p-1 {
  padding: 10px;
}
.p-2 {
  padding: 20px;
}
.pt-24 {
  padding-top: 24px;
}
.pt-25 {
  padding-top: 25px;
}
.pb-25 {
  padding-bottom: 25px;
}
.p-0 {
  padding: 0 !important;
}
.pr-0 {
  padding-right: 0 !important;
}
.pr-05 {
  padding-right: 5px !important;
}
.pr-08 {
  padding-right: 8px !important;
}
.pr-09 {
  padding-right: 9px !important;
}
.pr-2 {
  padding-right: 20px !important;
}
.pr-23 {
  padding-right: 23px !important;
}
.pr-24 {
  padding-right: 24px !important;
}
.pl-05 {
  padding-left: 5px !important;
}
.pl-2 {
  padding-left: 20px !important;
}
.pl-22 {
  padding-left: 22px !important;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.img-responsive {
  max-width: 100%;
  height: auto;
}
.text-left {
  text-align-last: left;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.w-100 {
  width: 100%;
}
.f-r {
  float: right;
}
.red-decorater {
  color: #e00b3d;
}
.blue-background-decorator {
  background-color: #29d1d4;
}
.red-background-decorator {
  background-color: #e00b3d;
}
.loading-animation {
  -webkit-animation: spinner 1s infinite linear;
  top: 20% !important;
}
@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate3d(0, 0, 1, 0deg);
    -ms-transform: rotate3d(0, 0, 1, 0deg);
    -o-transform: rotate3d(0, 0, 1, 0deg);
    transform: rotate3d(0, 0, 1, 0deg);
  }
  50% {
    -webkit-transform: rotate3d(0, 0, 1, 180deg);
    -ms-transform: rotate3d(0, 0, 1, 180deg);
    -o-transform: rotate3d(0, 0, 1, 180deg);
    transform: rotate3d(0, 0, 1, 180deg);
  }
  100% {
    -webkit-transform: rotate3d(0, 0, 1, 360deg);
    -ms-transform: rotate3d(0, 0, 1, 360deg);
    -o-transform: rotate3d(0, 0, 1, 360deg);
    transform: rotate3d(0, 0, 1, 360deg);
  }
}
.half-opacity {
  opacity: 0.5;
}
.border-right {
  border-right: 2px solid #dcdcdc;
}
@media screen and (max-width: -1px) {
  .hidden-xs-down {
    display: none !important;
  }
}
.hidden-xs-up {
  display: none !important;
}
@media screen and (max-width: 219px) {
  .hidden-xs-down {
    display: none !important;
  }
}
@media screen and (min-width: 220px) {
  .hidden-xs-up {
    display: none !important;
  }
}
@media screen and (max-width: 639px) {
  .hidden-sm-down {
    display: none !important;
  }
}
@media screen and (min-width: 640px) {
  .hidden-sm-up {
    display: none !important;
  }
}
@media screen and (max-width: 767px) {
  .hidden-md-down {
    display: none !important;
  }
}
@media screen and (min-width: 768px) {
  .hidden-md-up {
    display: none !important;
  }
}
@media screen and (max-width: 991px) {
  .hidden-lg-down {
    display: none !important;
  }
}
@media screen and (min-width: 992px) {
  .hidden-lg-up {
    display: none !important;
  }
}
@media screen and (max-width: 1199px) {
  .hidden-xl-down {
    display: none !important;
  }
}
@media screen and (min-width: 1200px) {
  .hidden-xl-up {
    display: none !important;
  }
}
@media screen and (max-width: 1599px) {
  .hidden-xxl-down {
    display: none !important;
  }
}
@media screen and (min-width: 1600px) {
  .hidden-xxl-up {
    display: none !important;
  }
}
.container--fluid {
  margin: 0;
  max-width: 100%;
}
.container__row {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
  margin-right: -12px;
  margin-left: -12px;
}
.container__col-offset-0 {
  margin-left: 0;
}
.container__col-1 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 8.3333333333%;
  flex: 0 0 8.3333333333%;
  max-width: 8.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-1 {
  margin-left: 8.3333333333%;
}
.container__col-2 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 16.6666666667%;
  flex: 0 0 16.6666666667%;
  max-width: 16.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-2 {
  margin-left: 16.6666666667%;
}
.container__col-3 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 25%;
  flex: 0 0 25%;
  max-width: 25%;
  margin-bottom: 10px;
}
.container__col-offset-3 {
  margin-left: 25%;
}
.container__col-4 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 33.3333333333%;
  flex: 0 0 33.3333333333%;
  max-width: 33.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-4 {
  margin-left: 33.3333333333%;
}
.container__col-5 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 41.6666666667%;
  flex: 0 0 41.6666666667%;
  max-width: 41.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-5 {
  margin-left: 41.6666666667%;
}
.container__col-6 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 50%;
  flex: 0 0 50%;
  max-width: 50%;
  margin-bottom: 10px;
}
.container__col-offset-6 {
  margin-left: 50%;
}
.container__col-7 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 58.3333333333%;
  flex: 0 0 58.3333333333%;
  max-width: 58.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-7 {
  margin-left: 58.3333333333%;
}
.container__col-8 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 66.6666666667%;
  flex: 0 0 66.6666666667%;
  max-width: 66.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-8 {
  margin-left: 66.6666666667%;
}
.container__col-9 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 75%;
  flex: 0 0 75%;
  max-width: 75%;
  margin-bottom: 10px;
}
.container__col-offset-9 {
  margin-left: 75%;
}
.container__col-10 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 83.3333333333%;
  flex: 0 0 83.3333333333%;
  max-width: 83.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-10 {
  margin-left: 83.3333333333%;
}
.container__col-11 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 91.6666666667%;
  flex: 0 0 91.6666666667%;
  max-width: 91.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-11 {
  margin-left: 91.6666666667%;
}
.container__col-12 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 100%;
  flex: 0 0 100%;
  max-width: 100%;
  margin-bottom: 10px;
}
.container__col-offset-12 {
  margin-left: 100%;
}
@media screen and (min-width: 220px) {
  .container__col-xs-offset-0 {
    margin-left: 0;
  }
  .container__col-xs-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xs-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xs-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-3 {
    margin-left: 25%;
  }
  .container__col-xs-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xs-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xs-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-6 {
    margin-left: 50%;
  }
  .container__col-xs-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xs-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xs-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-9 {
    margin-left: 75%;
  }
  .container__col-xs-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xs-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xs-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 640px) {
  .container__col-sm-offset-0 {
    margin-left: 0;
  }
  .container__col-sm-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-sm-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-sm-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-3 {
    margin-left: 25%;
  }
  .container__col-sm-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-sm-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-sm-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-6 {
    margin-left: 50%;
  }
  .container__col-sm-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-sm-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-sm-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-9 {
    margin-left: 75%;
  }
  .container__col-sm-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-sm-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-sm-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 768px) {
  .container__col-md-offset-0 {
    margin-left: 0;
  }
  .container__col-md-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-md-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-md-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-3 {
    margin-left: 25%;
  }
  .container__col-md-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-md-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-md-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-6 {
    margin-left: 50%;
  }
  .container__col-md-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-md-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-md-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-9 {
    margin-left: 75%;
  }
  .container__col-md-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-md-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-md-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 992px) {
  .container__col-lg-offset-0 {
    margin-left: 0;
  }
  .container__col-lg-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-lg-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-lg-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-3 {
    margin-left: 25%;
  }
  .container__col-lg-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-lg-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-lg-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-6 {
    margin-left: 50%;
  }
  .container__col-lg-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-lg-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-lg-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-9 {
    margin-left: 75%;
  }
  .container__col-lg-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-lg-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-lg-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 1200px) {
  .container__col-xl-offset-0 {
    margin-left: 0;
  }
  .container__col-xl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 1600px) {
  .container__col-xxl-offset-0 {
    margin-left: 0;
  }
  .container__col-xxl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xxl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xxl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xxl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xxl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xxl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xxl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xxl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xxl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xxl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xxl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xxl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-12 {
    margin-left: 100%;
  }
}
.display-flex {
  display: flex;
}
.button {
  position: relative;
  border: none;
  outline: none;
  font-size: 12px;
  color: #ffffff;
  text-align: center;
  font-family: "Roboto Regular";
  padding: 8px 34px;
  cursor: pointer;
  line-height: 17px;
}
.button.linear {
  -webkit-border-radius: 15px;
  -moz-border-radius: 15px;
  -ms-border-radius: 15px;
  border-radius: 15px;
}
.button.squared {
  border-radius: 3px;
  padding: 6px;
}
.button.normal {
  border-radius: 0;
  padding: 5px 25px;
}
.button-regular-orange {
  background-color: #ff7200;
}
.button-regular-green {
  background-color: #7fb345;
}
.button-regular-blue {
  background-color: #29d1d4;
}
.button-regular-red {
  background-color: #e00b3d;
}
.button-regular-gray {
  background-color: #a7a9ac;
}
.button-bordered {
  background-color: transparent;
  padding: 7px 28px;
}
@media (max-width: 576px) {
  .button-bordered {
    padding: 6px 22px;
  }
}
.button-bordered-black {
  color: #373737;
  border: 1px solid #3e3e3e;
  background-color: transparent;
}
.button-bordered-orange {
  color: #ff7200;
  border: 1px solid #ff7200;
  background-color: transparent;
}
.button-bordered-green {
  color: #7fb345;
  border: 1px solid #7fb345;
  background-color: transparent;
}
.button-bordered-green:hover {
  color: #ffffff;
  background-color: #88b917;
}
.button-bordered-blue {
  color: #29d1d4;
  border: 1px solid #29d1d4;
  color: #29d1d4;
  background-color: transparent;
}
.button-bordered-red {
  color: #e00b3d;
  border: 1px solid #e00b3d;
  background-color: transparent;
}
.button-bordered-gray {
  color: #a7a9ac;
  border: 1px solid #a7a9ac;
  background-color: transparent;
}
.button-bordered-white {
  border: 1px solid #ffffff;
  background-color: transparent;
}
.button-validate-blue {
  background-color: #009fdf;
}
.button-validate-red {
  background-color: #e00b3d;
}
.button-validate-green {
  background-color: #84bd00;
}
.button .icon-action-clock:before {
  content: "";
  position: absolute;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: #88bd23;
  bottom: 2px;
  left: 4px;
}
.button.icon {
  padding: 2px 14px;
  border-radius: 2px;
}
.button.icon > span {
  position: static;
  transform: none;
  font-size: 20px;
}
.button-card-position {
  position: absolute;
  bottom: 20px;
}`;const ze=({children:n,label:e,onClick:o,buttonType:i,color:a,iconActionType:r,customClass:s,customSecond:l,style:p,iconColor:d,iconPosition:m,position:b,...f})=>{const x=S(Fe.button,{[Fe[`button-${i}-${a}`]]:!0},Fe.linear,Fe[s||""],Fe[l||""],Fe[`button-${m}`],Fe[b||""]);return c.createElement("button",{className:x,style:p,onClick:o,...f},r?c.createElement(Di,{iconActionType:r,iconColor:d,iconDirection:"icon-position-right"}):null,e,n)};var Ot=`@charset "UTF-8";
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
/* Colors */
/* Fonts */
.button-action {
  position: relative;
  cursor: pointer;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: 1px solid #cdcdcd;
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.button-action-icon-delete:after {
  content: "\uE903";
  color: #cdcdcd;
}
.button-action-icon-delete.gray {
  color: #dcdcdc;
}
.button-action-card-position {
  position: absolute;
  bottom: 20px;
  right: 15px;
}`;const Vx=({buttonActionType:n,buttonIconType:e,onClick:o,iconColor:i,title:a,customPosition:r})=>{const s=S(Ot["button-action"],{[Ot[`button-action-${n||""}`]]:!0},Ot[r||""],Ot[i]);return c.createElement("span",{className:s,onClick:o},c.createElement(Di,{iconActionType:e,iconColor:i||""}),a&&c.createElement("span",{className:Ot["button-action-title"]},a))};var Hi=`@charset "UTF-8";
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
@font-face {
  font-family: "icomoon";
  src: url("__VITE_ASSET__86322399__");
  src: url("__VITE_ASSET__86322399__") format("embedded-opentype"), url("__VITE_ASSET__434e2b1b__") format("truetype"), url("__VITE_ASSET__8b06532b__") format("woff"), url("__VITE_ASSET__9d46154f__") format("svg");
  font-weight: normal;
  font-style: normal;
}
.icon-action {
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  display: inline-block;
  font-size: 25px;
}
.icon-action-update:after {
  content: "\uE911";
  color: #dcdcdc;
}
.icon-action-update.white:after {
  color: #ffffff;
}
.icon-action-update.gray:after {
  color: #cdcdcd;
}
.icon-action-update.orange:after {
  color: #ff7200;
}
.icon-action-add:after {
  content: "\uE921";
  color: #dcdcdc;
}
.icon-action-add.white:after {
  color: #ffffff;
}
.icon-action-add.gray:after {
  color: #cdcdcd;
}
.icon-action-add.orange:after {
  color: #ff7200;
}
.icon-action-delete:after {
  content: "\uE910";
  color: #dcdcdc;
}
.icon-action-delete.white:after {
  color: #ffffff;
}
.icon-action-delete.gray:after {
  color: #cdcdcd;
}
.icon-action-delete.orange:after {
  color: #ff7200;
}
.icon-action-delete-white:after {
  content: "\uE910";
  color: #ffffff;
}
.icon-action-clock:after {
  content: "\uE914";
  color: #dcdcdc;
}
.icon-action-clock.white:after {
  color: #ffffff;
}
.icon-action-clock.gray:after {
  color: #cdcdcd;
}
.icon-action-clock.orange:after {
  color: #ff7200;
}
.icon-action-check:after {
  content: "\uE913";
  color: #dcdcdc;
}
.icon-action-check.white:after {
  color: #ffffff;
}
.icon-action-check.gray:after {
  color: #cdcdcd;
}
.icon-action-check.green:after {
  color: #43b02a;
}
.icon-action-check.orange:after {
  color: #ff7200;
}
.icon-action-warning:after {
  content: "\uE920";
  color: #dcdcdc;
}
.icon-action-warning.white:after {
  color: #ffffff;
}
.icon-action-warning.gray:after {
  color: #cdcdcd;
}
.icon-action-warning.red:after {
  color: #e00b3d;
}
.icon-action-warning.orange:after {
  color: #ff7200;
}
.icon-action-arrow-right:after {
  content: "\uE923";
  color: #dcdcdc;
}
.icon-action-arrow-right.white:after {
  color: #ffffff;
}
.icon-action-arrow-right.gray:after {
  color: #cdcdcd;
}
.icon-action-arrow-right.red:after {
  color: #e00b3d;
}
.icon-action-arrow-right.orange:after {
  color: #ff7200;
}
.icon-action-arrow-left:after {
  content: "\uE924";
  color: #dcdcdc;
}
.icon-action-arrow-left.white:after {
  color: #ffffff;
}
.icon-action-arrow-left.gray:after {
  color: #cdcdcd;
}
.icon-action-arrow-left.red:after {
  color: #e00b3d;
}
.icon-action-arrow-left.orange:after {
  color: #ff7200;
}
.icon-position-left, .icon-position-right, .icon-position-center {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
}
.icon-position-left {
  left: 5px;
}
.icon-position-right {
  right: 5px;
}
.icon-position-center {
  left: 50%;
  transform: translate(-50%, -50%);
}
.icon-position-reset {
  position: static;
  transform: none;
}
.icon-position-counter {
  position: absolute;
  transform: rotate(90deg);
  right: 1px;
  top: 3px;
  font-size: 20px;
  cursor: pointer;
}
.icon-action-custom {
  right: 0;
  z-index: 1;
  position: absolute;
  font-size: 22px;
}
.icon-action-custom:after {
  color: #7fb345;
}
.button-action-input {
  position: relative;
  cursor: pointer;
  padding: 6px 7px 7px 7px;
  display: inline-block;
}
.button-action-input.green {
  background-color: #84bd00;
}
.button-action .content-icon {
  display: inline-block;
  position: absolute;
  right: 6px;
  width: 21px;
  height: 20px;
  top: 50%;
  transform: translateY(-50%);
}
.button-action-icon-custom {
  position: absolute;
  right: -39px;
}`;const jx=({buttonIconType:n,buttonColor:e,iconColor:o,buttonPosition:i,onClick:a})=>{const r=S(Hi["button-action-input"],Hi[e||""],Hi[i||""]);return c.createElement("span",{className:r,onClick:a},c.createElement(Di,{iconActionType:n,iconColor:o||""}))},qx=Z(n=>({button:{padding:n.spacing(.25)}})),Rn=({title:n,ariaLabel:e,...o})=>{const i=qx();return c.createElement(er,{"aria-label":e,title:n},c.createElement("span",null,c.createElement(Je,{className:i.button,color:"primary",...o})))};var Pe=`/* Colors */
/* Fonts */
@font-face {
  font-family: "Roboto Light";
  src: url("__VITE_ASSET__6f6d7b33__") format("woff2"), url("__VITE_ASSET__2f0e40ac__") format("woff"), url("__VITE_ASSET__a6d343d4__") format("truetype");
}
@font-face {
  font-family: "Roboto Regular";
  src: url("__VITE_ASSET__b11b2aeb__") format("woff2"), url("__VITE_ASSET__91658dab__") format("woff"), url("__VITE_ASSET__79e85140__") format("truetype");
}
@font-face {
  font-family: "Roboto Medium";
  src: url("__VITE_ASSET__48afa2e1__") format("woff2"), url("__VITE_ASSET__96cff21a__") format("woff"), url("__VITE_ASSET__b1b55bae__") format("truetype");
}
@font-face {
  font-family: "Roboto Bold";
  src: url("__VITE_ASSET__2adae71b__") format("woff2"), url("__VITE_ASSET__16e6f826__") format("woff"), url("__VITE_ASSET__37f5abe1__") format("truetype");
}
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
/* Colors */
/* Fonts */
.container {
  padding: 20px;
}
.container-gray {
  background-color: #f9f9f9;
}
.container-gray .container__col-md-3,
.container-gray .container__col-md-4,
.container-gray .container__col-sm-6,
.container-gray .container__col-md-9 {
  margin: 0;
}
.container-blue {
  background-color: #29d1d4;
}
.container-red {
  background-color: #e00b3d;
}
.content-wrapper {
  padding: 12px 20px 0 20px;
  box-sizing: border-box;
  margin: 0 auto;
}
@media (max-width: 767px) {
  .content-wrapper {
    padding: 12px;
  }
}
.content-wrap {
  height: 100vh;
  display: flex;
  flex-direction: column;
}
.content-inner {
  flex: 1;
  overflow: auto;
}
.content-overflow {
  flex: 1;
  min-height: 0px;
}
/* Colors */
/* Fonts */
.m-0 {
  margin: 0 !important;
}
.mb-0 {
  margin-bottom: 0 !important;
}
.mb-1 {
  margin-bottom: 10px;
}
.mb-2 {
  margin-bottom: 20px;
}
.mr-2 {
  margin-right: 20px;
}
.mr-4 {
  margin-right: 40px;
}
.ml-1 {
  margin-left: 10px;
}
.ml-2 {
  margin-left: 20px;
}
.mt-03 {
  margin-top: 3px !important;
}
.mt-05 {
  margin-top: 5px !important;
}
.mt-1 {
  margin-top: 10px;
}
.mt-2 {
  margin-top: 20px;
}
.p-1 {
  padding: 10px;
}
.p-2 {
  padding: 20px;
}
.pt-24 {
  padding-top: 24px;
}
.pt-25 {
  padding-top: 25px;
}
.pb-25 {
  padding-bottom: 25px;
}
.p-0 {
  padding: 0 !important;
}
.pr-0 {
  padding-right: 0 !important;
}
.pr-05 {
  padding-right: 5px !important;
}
.pr-08 {
  padding-right: 8px !important;
}
.pr-09 {
  padding-right: 9px !important;
}
.pr-2 {
  padding-right: 20px !important;
}
.pr-23 {
  padding-right: 23px !important;
}
.pr-24 {
  padding-right: 24px !important;
}
.pl-05 {
  padding-left: 5px !important;
}
.pl-2 {
  padding-left: 20px !important;
}
.pl-22 {
  padding-left: 22px !important;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.img-responsive {
  max-width: 100%;
  height: auto;
}
.text-left {
  text-align-last: left;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.w-100 {
  width: 100%;
}
.f-r {
  float: right;
}
.red-decorater {
  color: #e00b3d;
}
.blue-background-decorator {
  background-color: #29d1d4;
}
.red-background-decorator {
  background-color: #e00b3d;
}
.loading-animation {
  -webkit-animation: spinner 1s infinite linear;
  top: 20% !important;
}
@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate3d(0, 0, 1, 0deg);
    -ms-transform: rotate3d(0, 0, 1, 0deg);
    -o-transform: rotate3d(0, 0, 1, 0deg);
    transform: rotate3d(0, 0, 1, 0deg);
  }
  50% {
    -webkit-transform: rotate3d(0, 0, 1, 180deg);
    -ms-transform: rotate3d(0, 0, 1, 180deg);
    -o-transform: rotate3d(0, 0, 1, 180deg);
    transform: rotate3d(0, 0, 1, 180deg);
  }
  100% {
    -webkit-transform: rotate3d(0, 0, 1, 360deg);
    -ms-transform: rotate3d(0, 0, 1, 360deg);
    -o-transform: rotate3d(0, 0, 1, 360deg);
    transform: rotate3d(0, 0, 1, 360deg);
  }
}
.half-opacity {
  opacity: 0.5;
}
.border-right {
  border-right: 2px solid #dcdcdc;
}
@media screen and (max-width: -1px) {
  .hidden-xs-down {
    display: none !important;
  }
}
.hidden-xs-up {
  display: none !important;
}
@media screen and (max-width: 219px) {
  .hidden-xs-down {
    display: none !important;
  }
}
@media screen and (min-width: 220px) {
  .hidden-xs-up {
    display: none !important;
  }
}
@media screen and (max-width: 639px) {
  .hidden-sm-down {
    display: none !important;
  }
}
@media screen and (min-width: 640px) {
  .hidden-sm-up {
    display: none !important;
  }
}
@media screen and (max-width: 767px) {
  .hidden-md-down {
    display: none !important;
  }
}
@media screen and (min-width: 768px) {
  .hidden-md-up {
    display: none !important;
  }
}
@media screen and (max-width: 991px) {
  .hidden-lg-down {
    display: none !important;
  }
}
@media screen and (min-width: 992px) {
  .hidden-lg-up {
    display: none !important;
  }
}
@media screen and (max-width: 1199px) {
  .hidden-xl-down {
    display: none !important;
  }
}
@media screen and (min-width: 1200px) {
  .hidden-xl-up {
    display: none !important;
  }
}
@media screen and (max-width: 1599px) {
  .hidden-xxl-down {
    display: none !important;
  }
}
@media screen and (min-width: 1600px) {
  .hidden-xxl-up {
    display: none !important;
  }
}
.container--fluid {
  margin: 0;
  max-width: 100%;
}
.container__row {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
  margin-right: -12px;
  margin-left: -12px;
}
.container__col-offset-0 {
  margin-left: 0;
}
.container__col-1 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 8.3333333333%;
  flex: 0 0 8.3333333333%;
  max-width: 8.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-1 {
  margin-left: 8.3333333333%;
}
.container__col-2 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 16.6666666667%;
  flex: 0 0 16.6666666667%;
  max-width: 16.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-2 {
  margin-left: 16.6666666667%;
}
.container__col-3 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 25%;
  flex: 0 0 25%;
  max-width: 25%;
  margin-bottom: 10px;
}
.container__col-offset-3 {
  margin-left: 25%;
}
.container__col-4 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 33.3333333333%;
  flex: 0 0 33.3333333333%;
  max-width: 33.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-4 {
  margin-left: 33.3333333333%;
}
.container__col-5 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 41.6666666667%;
  flex: 0 0 41.6666666667%;
  max-width: 41.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-5 {
  margin-left: 41.6666666667%;
}
.container__col-6 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 50%;
  flex: 0 0 50%;
  max-width: 50%;
  margin-bottom: 10px;
}
.container__col-offset-6 {
  margin-left: 50%;
}
.container__col-7 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 58.3333333333%;
  flex: 0 0 58.3333333333%;
  max-width: 58.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-7 {
  margin-left: 58.3333333333%;
}
.container__col-8 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 66.6666666667%;
  flex: 0 0 66.6666666667%;
  max-width: 66.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-8 {
  margin-left: 66.6666666667%;
}
.container__col-9 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 75%;
  flex: 0 0 75%;
  max-width: 75%;
  margin-bottom: 10px;
}
.container__col-offset-9 {
  margin-left: 75%;
}
.container__col-10 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 83.3333333333%;
  flex: 0 0 83.3333333333%;
  max-width: 83.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-10 {
  margin-left: 83.3333333333%;
}
.container__col-11 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 91.6666666667%;
  flex: 0 0 91.6666666667%;
  max-width: 91.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-11 {
  margin-left: 91.6666666667%;
}
.container__col-12 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 100%;
  flex: 0 0 100%;
  max-width: 100%;
  margin-bottom: 10px;
}
.container__col-offset-12 {
  margin-left: 100%;
}
@media screen and (min-width: 220px) {
  .container__col-xs-offset-0 {
    margin-left: 0;
  }
  .container__col-xs-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xs-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xs-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-3 {
    margin-left: 25%;
  }
  .container__col-xs-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xs-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xs-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-6 {
    margin-left: 50%;
  }
  .container__col-xs-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xs-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xs-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-9 {
    margin-left: 75%;
  }
  .container__col-xs-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xs-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xs-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 640px) {
  .container__col-sm-offset-0 {
    margin-left: 0;
  }
  .container__col-sm-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-sm-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-sm-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-3 {
    margin-left: 25%;
  }
  .container__col-sm-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-sm-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-sm-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-6 {
    margin-left: 50%;
  }
  .container__col-sm-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-sm-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-sm-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-9 {
    margin-left: 75%;
  }
  .container__col-sm-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-sm-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-sm-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 768px) {
  .container__col-md-offset-0 {
    margin-left: 0;
  }
  .container__col-md-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-md-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-md-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-3 {
    margin-left: 25%;
  }
  .container__col-md-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-md-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-md-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-6 {
    margin-left: 50%;
  }
  .container__col-md-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-md-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-md-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-9 {
    margin-left: 75%;
  }
  .container__col-md-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-md-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-md-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 992px) {
  .container__col-lg-offset-0 {
    margin-left: 0;
  }
  .container__col-lg-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-lg-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-lg-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-3 {
    margin-left: 25%;
  }
  .container__col-lg-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-lg-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-lg-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-6 {
    margin-left: 50%;
  }
  .container__col-lg-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-lg-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-lg-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-9 {
    margin-left: 75%;
  }
  .container__col-lg-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-lg-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-lg-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 1200px) {
  .container__col-xl-offset-0 {
    margin-left: 0;
  }
  .container__col-xl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 1600px) {
  .container__col-xxl-offset-0 {
    margin-left: 0;
  }
  .container__col-xxl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xxl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xxl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xxl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xxl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xxl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xxl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xxl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xxl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xxl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xxl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xxl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-12 {
    margin-left: 100%;
  }
}
.display-flex {
  display: flex;
}
.button {
  position: relative;
  border: none;
  outline: none;
  font-size: 12px;
  color: #ffffff;
  text-align: center;
  font-family: "Roboto Regular";
  padding: 8px 34px;
  cursor: pointer;
  line-height: 17px;
}
.button.linear {
  -webkit-border-radius: 15px;
  -moz-border-radius: 15px;
  -ms-border-radius: 15px;
  border-radius: 15px;
}
.button.squared {
  border-radius: 3px;
  padding: 6px;
}
.button.normal {
  border-radius: 0;
  padding: 5px 25px;
}
.button-regular-orange {
  background-color: #ff7200;
}
.button-regular-green {
  background-color: #7fb345;
}
.button-regular-blue {
  background-color: #29d1d4;
}
.button-regular-red {
  background-color: #e00b3d;
}
.button-regular-gray {
  background-color: #a7a9ac;
}
.button-bordered {
  background-color: transparent;
  padding: 7px 28px;
}
@media (max-width: 576px) {
  .button-bordered {
    padding: 6px 22px;
  }
}
.button-bordered-black {
  color: #373737;
  border: 1px solid #3e3e3e;
  background-color: transparent;
}
.button-bordered-orange {
  color: #ff7200;
  border: 1px solid #ff7200;
  background-color: transparent;
}
.button-bordered-green {
  color: #7fb345;
  border: 1px solid #7fb345;
  background-color: transparent;
}
.button-bordered-green:hover {
  color: #ffffff;
  background-color: #88b917;
}
.button-bordered-blue {
  color: #29d1d4;
  border: 1px solid #29d1d4;
  color: #29d1d4;
  background-color: transparent;
}
.button-bordered-red {
  color: #e00b3d;
  border: 1px solid #e00b3d;
  background-color: transparent;
}
.button-bordered-gray {
  color: #a7a9ac;
  border: 1px solid #a7a9ac;
  background-color: transparent;
}
.button-bordered-white {
  border: 1px solid #ffffff;
  background-color: transparent;
}
.button-validate-blue {
  background-color: #009fdf;
}
.button-validate-red {
  background-color: #e00b3d;
}
.button-validate-green {
  background-color: #84bd00;
}
.button .icon-action-clock:before {
  content: "";
  position: absolute;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: #88bd23;
  bottom: 2px;
  left: 4px;
}
.button.icon {
  padding: 2px 14px;
  border-radius: 2px;
}
.button.icon > span {
  position: static;
  transform: none;
  font-size: 20px;
}
.button-card-position {
  position: absolute;
  bottom: 20px;
}
/* Colors */
/* Fonts */
@font-face {
  font-family: "Roboto Light";
  src: url("__VITE_ASSET__6f6d7b33__") format("woff2"), url("__VITE_ASSET__2f0e40ac__") format("woff"), url("__VITE_ASSET__a6d343d4__") format("truetype");
}
@font-face {
  font-family: "Roboto Regular";
  src: url("__VITE_ASSET__b11b2aeb__") format("woff2"), url("__VITE_ASSET__91658dab__") format("woff"), url("__VITE_ASSET__79e85140__") format("truetype");
}
@font-face {
  font-family: "Roboto Medium";
  src: url("__VITE_ASSET__48afa2e1__") format("woff2"), url("__VITE_ASSET__96cff21a__") format("woff"), url("__VITE_ASSET__b1b55bae__") format("truetype");
}
@font-face {
  font-family: "Roboto Bold";
  src: url("__VITE_ASSET__2adae71b__") format("woff2"), url("__VITE_ASSET__16e6f826__") format("woff"), url("__VITE_ASSET__37f5abe1__") format("truetype");
}
.custom-title {
  font-size: 22px;
  margin: 0;
  margin-bottom: 5px;
  color: #06a096;
  font-family: "Roboto Light";
  position: relative;
  cursor: pointer;
  font-weight: bold;
}
.custom-title-styles {
  padding-left: 55px;
  max-width: 190px;
}
.custom-title-styles .custom-title-icon {
  position: absolute;
  left: 0;
}
.custom-title-styles .custom-title-label {
  padding-left: 0 !important;
  min-height: 0 !important;
}
.custom-title-icon {
  width: 51px;
  height: 51px;
  position: absolute;
  left: 0;
  top: 0;
}
.custom-title-icon-puzzle {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADMAAAAzCAMAAAANf8AYAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAnZQTFRFAAAAAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAjR6tAAAANJ0Uk5TAAx+4v/HVhbU7Ky8oNB1BRyCU5XqFJ9rVCyYljsBDTdtHe/khS2GclhlKZLuzGkaQ44I4/LpBqi3Vw4HtifIf0SEYs38kX2bUJCvKi536/36TP7bPl0biUlcZMPOFwOXlEIgwCbW6Hkz8dEJTrmwNab7LyGcQXbfZ0B49rgkboFNE4eNaNIPKN3zsdWDS3RwPPfw+QTgW8URo6FqpXrGOfUCCkjErSvt2Fq14TKeFecQtD9VRzGpJYpRi8FmNDjaC3vX9ICPmq4Sp5O6siMecdOZesyw1wAAA2hJREFUeJyVlvkjVUEUx4e+kS1C9n2rlxYhxctSSZLCI4o2svasIRVlJ6lIRIv2SNpp16JN+/IfNfdej3m5w+v8MHO+58znzdy5M+c+Qvimpz8LmG0wzYgpZjgHRsYmgOl/MGaYa06IhQnm6YxYwkpyrDFfV8YGFpJjCzv+KHsHRyfnCWUMF8lxhRsXcfcA4OnlPS594Co5C7CQy1hBsch3MbBk6TJB+mG5GPYPgCEPcQ5cQdsgv5V0tlXBIUSJ1UI4NAzh3GkisEZyDNeuAyLXR2EDFdHARi5CYrBpwo/dHABsoU/mHRefwEf0VIlaOmlrMm1TsI2PkO2yO5qKNEt/LrMDO2Wiu3YDcXvS5ZEM7JVPZM7LArJzYmVSUYjmLiHXKg/Yl+r+b9wTai5DiDq/gL61wiKtYBGKp0EEc1fsQMl+NpKC0hkYImyiIyvLoCg/YFKx6uB0zCEcZqVrJV0vKuj1555HQqqgvQ1HjlbX1BLDOpiZ85BapMkn6rGWxzRgq3xCHc/5MUIa0cSo5uKVmttJjrVwkAwcZ5RLNn3+csnfVdnKYU7gJKNOoa0dp6WJg+Eljzh3nOlkZFc8IWfR3UNdJ1VksjzTril3otnjHG3Po8ThQgxgyVnaReQyaiPE+9HbQp/q0mUO4gJrrYVeuSo619qv23MIoRIrGJWEeu7ISXPDKQsD0xuFDWKhvYk+HZiMfkh2K5SQEBzTSg7czht0vDMVOqJ/N7xP71447j8gAzjKZJQPgcYtQA53QlukkG6telOFMnfS+cgDQ1xoMJDgBqOHESP2pZFGQey4zuq7jzX+ExBEMDkbPJWcZ5rzJ5lQQZ6P+2mUecHUyZfQk5yTGGGQV3joZ4Rm0e9RrSOJUE3WSRuMv9U2re9UGWLJa7wRPqu1YRgmb0dbaZ08LNVJO02VeMfujHmLJ22r8f5DalQJPoqx3E+0TjYqaIG4hzEh8PkLvjLTJGBU6BroO4B1myaqrvlGddXrfJRRVdSP784MU4AfYq9MTy9XsluTTOukdF1/duAXm2nCb8I1b33fYdrV4bpWWIFpK6VoPqirZXWWDv9DLOYAYwYT5yATBTMi9KSMdAMq30eS8sKwDgy1Zv0u+p+kV3jD/fHKGYdr7NCfSCBsqFfcfp1NPXSc7n72lC/TX19Iq71TBD92AAAAAElFTkSuQmCC") no-repeat 50%;
}
.custom-title-icon-object {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC4AAAAuCAMAAABgZ9sFAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAH5QTFRFAAAA////////////////FqacBZ+V////DqKZ////////EKOaCaGXE6Sb////CaCXKKyk////H6mgDaKY////ObOr////GaedDKKY////IKmh////////////////HKifB6CWFKWbI6qi////J6yjIKmgEaSaCaGXE6Wb////3uiKUgAAACp0Uk5TAB8bAkLV/xPnCxHi89sH9bYSxOkZoCHQ7Q3CDyAVCcr52r8GuMPh9Nsc9Ah7sAAAAUdJREFUeJztlNtawjAQhFNZVlooSqWAp6qcqu//giaBJLMlwVx45edcleWf6bJZotRZxY3K14iMsnEaM3O24ZYMm22g8YRKZxhlhSuVazDh9qGULdFQGC4M5rmi6QxV01yEe8Od/Uz3LLKZCl1dQPiph+bhhDcNLYOoblemut4I+tHZDY4pFq8uwp+YVBIfhpfeHsOfL8KJ3Rwj+MsAD+FR/HWwKiE80TuteeMNEB7H9WjAAOEJHA0YnsTBAOFXcG/AMRm8CxK4Nby9f0AebYcrhrg1iLfrGQvRQl1T/O+RVlehdj/Qv6Yq2mWqdaJ9A9q6jcDiAc57yviNnllnB3mEYs/krl2aydcyVeZUj6LY+1PV+DL8kM7hqZ35x/8oHtmZQy+LAf/kGqQ3cmc3spdF14H2tiB/A7ay6hubFyvU+Yb4EkWd/Q0u0ydAKvdF0gAAAABJRU5ErkJggg==") no-repeat 50%;
}
.custom-title-icon + .custom-title-label {
  padding-left: 56px;
  min-height: 51px;
  display: inline-block;
}
.custom-title.image {
  padding-left: 65px;
}
.custom-title.image .custom-title-icon {
  left: 0;
}
.custom-title-label {
  text-overflow: ellipsis;
  max-height: 87px;
  overflow: hidden;
}
.custom-title-label.blue {
  color: #0072aa;
}
.custom-title-label.host {
  font-size: 16px;
  line-height: 20px;
  font-family: "Roboto Light";
  color: #009fdf;
  cursor: text;
}
.custom-title-label.bam {
  font-size: 16px;
  line-height: 20px;
  color: #88bd23;
}
.custom-title-label.host {
  font-size: 16px;
  line-height: 20px;
  color: #009fdf;
}
.custom-title-label-container {
  display: flex;
  flex-direction: column;
}
.card-item {
  padding: 8px 28px 60px 8px;
  position: relative;
  border-radius: 5px;
  min-height: 180px;
  height: 100%;
  box-sizing: border-box;
  width: 100%;
  cursor: pointer;
}
@media (max-width: 767px) {
  .card-item {
    margin-bottom: 12px;
  }
}
.card-item:hover {
  background-color: #F9F9F9;
}
.card-item-footer {
  display: block;
  position: absolute;
  bottom: -1px;
  border-bottom-left-radius: 5px;
  border-bottom-right-radius: 5px;
  left: 0;
  right: 0;
  text-align: center;
  font-size: 10px;
  line-height: 14px;
  font-family: "Roboto Regular";
  color: #ffffff;
}
.card-item-footer-green {
  background-color: #7fb345;
}
.card-item-footer-orange {
  background-color: #ff7200;
}
.card-item-footer-red {
  background-color: #e00b3d;
}
.card-item-footer-blue {
  background-color: #29d1d4;
}
.card-item-bordered-orange {
  border: 1px solid #ff7200;
}
.card-item-bordered-green {
  border: 1px solid #7fb345;
}
.card-item-bordered-gray {
  border: 1px solid #dadada;
}
.card-inline {
  display: inline-block;
  margin: 5px;
  height: 180px;
  width: 220px;
}
.card .container__col-md-3 {
  display: flex;
}
.card .custom-title-label {
  min-height: 0 !important;
}
.card .info {
  position: absolute;
}`;class Wx extends c.Component{render(){const{children:e,style:o}=this.props;return c.createElement("div",{className:S(Pe.card),style:o},c.createElement("div",null,e))}}class Kx extends c.Component{render(){const{children:e,itemBorderColor:o,itemFooterColor:i,itemFooterLabel:a,customClass:r,style:s}=this.props,l=S(Pe["card-item"],{[Pe[`card-item-bordered-${o||""}`]]:!0},Pe[r||""]),p=S(Pe["card-item-footer"],{[Pe[`card-item-footer-${i||""}`]]:!0});return c.createElement("div",{className:l,style:s},e,c.createElement("span",{className:p},a))}}var Bt=`/* Colors */
/* Fonts */
.content-description-date {
  font-size: 14px;
  line-height: 18px;
  color: #28a095;
  font-family: "Roboto Regular";
}
.content-description-title {
  font-size: 18px;
  line-height: 20px;
  font-family: "Roboto Regular";
  color: #b6b5b5;
}
.content-description-text, .content-description-release-note {
  font-size: 14px;
  line-height: 16px;
  font-family: "Roboto Regular";
}
.content-description-text {
  color: #b6b5b5;
}
.content-description-release-note {
  cursor: pointer;
  color: #28a095;
  text-decoration: underline;
}
.popup .content-description-title {
  margin: 37px 0;
}`;const So=({date:n,title:e,text:o,note:i,link:a})=>c.createElement(c.Fragment,null,n?c.createElement("span",{className:S(Bt["content-description-date"])},n):null,e?c.createElement("h3",{className:S(Bt["content-description-title"])},e):null,o?c.createElement("p",{className:S(Bt["content-description-text"])},o.split(`
`).map(r=>c.createElement("span",{key:r},r,c.createElement("br",null)))):null,i?c.createElement("span",{className:S(Bt["content-description-release-note"])},a?c.createElement("a",{className:S(Bt["content-description-release-note"]),href:i,target:"_blank"},i):i):null);var On=`/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
/* Colors */
/* Fonts */
.m-0 {
  margin: 0 !important;
}
.mb-0 {
  margin-bottom: 0 !important;
}
.mb-1 {
  margin-bottom: 10px;
}
.mb-2 {
  margin-bottom: 20px;
}
.mr-2 {
  margin-right: 20px;
}
.mr-4 {
  margin-right: 40px;
}
.ml-1 {
  margin-left: 10px;
}
.ml-2 {
  margin-left: 20px;
}
.mt-03 {
  margin-top: 3px !important;
}
.mt-05 {
  margin-top: 5px !important;
}
.mt-1 {
  margin-top: 10px;
}
.mt-2 {
  margin-top: 20px;
}
.p-1 {
  padding: 10px;
}
.p-2 {
  padding: 20px;
}
.pt-24 {
  padding-top: 24px;
}
.pt-25 {
  padding-top: 25px;
}
.pb-25 {
  padding-bottom: 25px;
}
.p-0 {
  padding: 0 !important;
}
.pr-0 {
  padding-right: 0 !important;
}
.pr-05 {
  padding-right: 5px !important;
}
.pr-08 {
  padding-right: 8px !important;
}
.pr-09 {
  padding-right: 9px !important;
}
.pr-2 {
  padding-right: 20px !important;
}
.pr-23 {
  padding-right: 23px !important;
}
.pr-24 {
  padding-right: 24px !important;
}
.pl-05 {
  padding-left: 5px !important;
}
.pl-2 {
  padding-left: 20px !important;
}
.pl-22 {
  padding-left: 22px !important;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.img-responsive {
  max-width: 100%;
  height: auto;
}
.text-left {
  text-align-last: left;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.w-100 {
  width: 100%;
}
.f-r {
  float: right;
}
.red-decorater {
  color: #e00b3d;
}
.blue-background-decorator {
  background-color: #29d1d4;
}
.red-background-decorator {
  background-color: #e00b3d;
}
.loading-animation {
  -webkit-animation: spinner 1s infinite linear;
  top: 20% !important;
}
@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate3d(0, 0, 1, 0deg);
    -ms-transform: rotate3d(0, 0, 1, 0deg);
    -o-transform: rotate3d(0, 0, 1, 0deg);
    transform: rotate3d(0, 0, 1, 0deg);
  }
  50% {
    -webkit-transform: rotate3d(0, 0, 1, 180deg);
    -ms-transform: rotate3d(0, 0, 1, 180deg);
    -o-transform: rotate3d(0, 0, 1, 180deg);
    transform: rotate3d(0, 0, 1, 180deg);
  }
  100% {
    -webkit-transform: rotate3d(0, 0, 1, 360deg);
    -ms-transform: rotate3d(0, 0, 1, 360deg);
    -o-transform: rotate3d(0, 0, 1, 360deg);
    transform: rotate3d(0, 0, 1, 360deg);
  }
}
.half-opacity {
  opacity: 0.5;
}
.border-right {
  border-right: 2px solid #dcdcdc;
}
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
/* Colors */
/* Fonts */
.container {
  padding: 20px;
}
.container-gray {
  background-color: #f9f9f9;
}
.container-gray .container__col-md-3,
.container-gray .container__col-md-4,
.container-gray .container__col-sm-6,
.container-gray .container__col-md-9 {
  margin: 0;
}
.container-blue {
  background-color: #29d1d4;
}
.container-red {
  background-color: #e00b3d;
}
.content-wrapper {
  padding: 12px 20px 0 20px;
  box-sizing: border-box;
  margin: 0 auto;
}
@media (max-width: 767px) {
  .content-wrapper {
    padding: 12px;
  }
}
.content-wrap {
  height: 100vh;
  display: flex;
  flex-direction: column;
}
.content-inner {
  flex: 1;
  overflow: auto;
}
.content-overflow {
  flex: 1;
  min-height: 0px;
}
/* Colors */
/* Fonts */
.m-0 {
  margin: 0 !important;
}
.mb-0 {
  margin-bottom: 0 !important;
}
.mb-1 {
  margin-bottom: 10px;
}
.mb-2 {
  margin-bottom: 20px;
}
.mr-2 {
  margin-right: 20px;
}
.mr-4 {
  margin-right: 40px;
}
.ml-1 {
  margin-left: 10px;
}
.ml-2 {
  margin-left: 20px;
}
.mt-03 {
  margin-top: 3px !important;
}
.mt-05 {
  margin-top: 5px !important;
}
.mt-1 {
  margin-top: 10px;
}
.mt-2 {
  margin-top: 20px;
}
.p-1 {
  padding: 10px;
}
.p-2 {
  padding: 20px;
}
.pt-24 {
  padding-top: 24px;
}
.pt-25 {
  padding-top: 25px;
}
.pb-25 {
  padding-bottom: 25px;
}
.p-0 {
  padding: 0 !important;
}
.pr-0 {
  padding-right: 0 !important;
}
.pr-05 {
  padding-right: 5px !important;
}
.pr-08 {
  padding-right: 8px !important;
}
.pr-09 {
  padding-right: 9px !important;
}
.pr-2 {
  padding-right: 20px !important;
}
.pr-23 {
  padding-right: 23px !important;
}
.pr-24 {
  padding-right: 24px !important;
}
.pl-05 {
  padding-left: 5px !important;
}
.pl-2 {
  padding-left: 20px !important;
}
.pl-22 {
  padding-left: 22px !important;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.img-responsive {
  max-width: 100%;
  height: auto;
}
.text-left {
  text-align-last: left;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.w-100 {
  width: 100%;
}
.f-r {
  float: right;
}
.red-decorater {
  color: #e00b3d;
}
.blue-background-decorator {
  background-color: #29d1d4;
}
.red-background-decorator {
  background-color: #e00b3d;
}
.loading-animation {
  -webkit-animation: spinner 1s infinite linear;
  top: 20% !important;
}
@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate3d(0, 0, 1, 0deg);
    -ms-transform: rotate3d(0, 0, 1, 0deg);
    -o-transform: rotate3d(0, 0, 1, 0deg);
    transform: rotate3d(0, 0, 1, 0deg);
  }
  50% {
    -webkit-transform: rotate3d(0, 0, 1, 180deg);
    -ms-transform: rotate3d(0, 0, 1, 180deg);
    -o-transform: rotate3d(0, 0, 1, 180deg);
    transform: rotate3d(0, 0, 1, 180deg);
  }
  100% {
    -webkit-transform: rotate3d(0, 0, 1, 360deg);
    -ms-transform: rotate3d(0, 0, 1, 360deg);
    -o-transform: rotate3d(0, 0, 1, 360deg);
    transform: rotate3d(0, 0, 1, 360deg);
  }
}
.half-opacity {
  opacity: 0.5;
}
.border-right {
  border-right: 2px solid #dcdcdc;
}
@media screen and (max-width: -1px) {
  .hidden-xs-down {
    display: none !important;
  }
}
.hidden-xs-up {
  display: none !important;
}
@media screen and (max-width: 219px) {
  .hidden-xs-down {
    display: none !important;
  }
}
@media screen and (min-width: 220px) {
  .hidden-xs-up {
    display: none !important;
  }
}
@media screen and (max-width: 639px) {
  .hidden-sm-down {
    display: none !important;
  }
}
@media screen and (min-width: 640px) {
  .hidden-sm-up {
    display: none !important;
  }
}
@media screen and (max-width: 767px) {
  .hidden-md-down {
    display: none !important;
  }
}
@media screen and (min-width: 768px) {
  .hidden-md-up {
    display: none !important;
  }
}
@media screen and (max-width: 991px) {
  .hidden-lg-down {
    display: none !important;
  }
}
@media screen and (min-width: 992px) {
  .hidden-lg-up {
    display: none !important;
  }
}
@media screen and (max-width: 1199px) {
  .hidden-xl-down {
    display: none !important;
  }
}
@media screen and (min-width: 1200px) {
  .hidden-xl-up {
    display: none !important;
  }
}
@media screen and (max-width: 1599px) {
  .hidden-xxl-down {
    display: none !important;
  }
}
@media screen and (min-width: 1600px) {
  .hidden-xxl-up {
    display: none !important;
  }
}
.container--fluid {
  margin: 0;
  max-width: 100%;
}
.container__row {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
  margin-right: -12px;
  margin-left: -12px;
}
.container__col-offset-0 {
  margin-left: 0;
}
.container__col-1 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 8.3333333333%;
  flex: 0 0 8.3333333333%;
  max-width: 8.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-1 {
  margin-left: 8.3333333333%;
}
.container__col-2 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 16.6666666667%;
  flex: 0 0 16.6666666667%;
  max-width: 16.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-2 {
  margin-left: 16.6666666667%;
}
.container__col-3 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 25%;
  flex: 0 0 25%;
  max-width: 25%;
  margin-bottom: 10px;
}
.container__col-offset-3 {
  margin-left: 25%;
}
.container__col-4 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 33.3333333333%;
  flex: 0 0 33.3333333333%;
  max-width: 33.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-4 {
  margin-left: 33.3333333333%;
}
.container__col-5 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 41.6666666667%;
  flex: 0 0 41.6666666667%;
  max-width: 41.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-5 {
  margin-left: 41.6666666667%;
}
.container__col-6 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 50%;
  flex: 0 0 50%;
  max-width: 50%;
  margin-bottom: 10px;
}
.container__col-offset-6 {
  margin-left: 50%;
}
.container__col-7 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 58.3333333333%;
  flex: 0 0 58.3333333333%;
  max-width: 58.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-7 {
  margin-left: 58.3333333333%;
}
.container__col-8 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 66.6666666667%;
  flex: 0 0 66.6666666667%;
  max-width: 66.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-8 {
  margin-left: 66.6666666667%;
}
.container__col-9 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 75%;
  flex: 0 0 75%;
  max-width: 75%;
  margin-bottom: 10px;
}
.container__col-offset-9 {
  margin-left: 75%;
}
.container__col-10 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 83.3333333333%;
  flex: 0 0 83.3333333333%;
  max-width: 83.3333333333%;
  margin-bottom: 10px;
}
.container__col-offset-10 {
  margin-left: 83.3333333333%;
}
.container__col-11 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 91.6666666667%;
  flex: 0 0 91.6666666667%;
  max-width: 91.6666666667%;
  margin-bottom: 10px;
}
.container__col-offset-11 {
  margin-left: 91.6666666667%;
}
.container__col-12 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 100%;
  flex: 0 0 100%;
  max-width: 100%;
  margin-bottom: 10px;
}
.container__col-offset-12 {
  margin-left: 100%;
}
@media screen and (min-width: 220px) {
  .container__col-xs-offset-0 {
    margin-left: 0;
  }
  .container__col-xs-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xs-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xs-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-3 {
    margin-left: 25%;
  }
  .container__col-xs-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xs-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xs-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-6 {
    margin-left: 50%;
  }
  .container__col-xs-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xs-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xs-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-9 {
    margin-left: 75%;
  }
  .container__col-xs-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xs-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xs-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 640px) {
  .container__col-sm-offset-0 {
    margin-left: 0;
  }
  .container__col-sm-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-sm-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-sm-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-3 {
    margin-left: 25%;
  }
  .container__col-sm-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-sm-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-sm-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-6 {
    margin-left: 50%;
  }
  .container__col-sm-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-sm-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-sm-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-9 {
    margin-left: 75%;
  }
  .container__col-sm-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-sm-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-sm-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 768px) {
  .container__col-md-offset-0 {
    margin-left: 0;
  }
  .container__col-md-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-md-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-md-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-3 {
    margin-left: 25%;
  }
  .container__col-md-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-md-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-md-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-6 {
    margin-left: 50%;
  }
  .container__col-md-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-md-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-md-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-9 {
    margin-left: 75%;
  }
  .container__col-md-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-md-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-md-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 992px) {
  .container__col-lg-offset-0 {
    margin-left: 0;
  }
  .container__col-lg-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-lg-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-lg-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-3 {
    margin-left: 25%;
  }
  .container__col-lg-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-lg-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-lg-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-6 {
    margin-left: 50%;
  }
  .container__col-lg-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-lg-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-lg-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-9 {
    margin-left: 75%;
  }
  .container__col-lg-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-lg-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-lg-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 1200px) {
  .container__col-xl-offset-0 {
    margin-left: 0;
  }
  .container__col-xl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-12 {
    margin-left: 100%;
  }
}
@media screen and (min-width: 1600px) {
  .container__col-xxl-offset-0 {
    margin-left: 0;
  }
  .container__col-xxl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xxl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xxl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xxl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xxl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xxl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xxl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xxl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xxl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xxl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xxl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xxl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-12 {
    margin-left: 100%;
  }
}
.display-flex {
  display: flex;
}
.popup {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1050;
  overflow: hidden;
  outline: 0;
  overflow-x: hidden;
  overflow-y: auto;
}
.popup-dialog {
  margin: 20px;
  position: relative;
  width: auto;
}
@media (min-width: 840px) {
  .popup-dialog {
    margin: 20px auto;
  }
}
@media (max-width: 840px) {
  .popup-dialog {
    margin: 20px 30px;
  }
}
.popup-header, .popup-body, .popup-footer {
  padding: 20px;
}
.popup.popup-small .popup-header, .popup.popup-small .popup-body, .popup.popup-small .popup-footer {
  padding: 20px;
}
.popup.popup-small .popup-body {
  padding: 5px 25px;
}
.popup.popup-small.scroll .popup-body {
  max-height: 350px;
  overflow-y: auto;
}
.popup.popup-small.scroll .popup-body::-webkit-scrollbar {
  width: 5px;
}
.popup.popup-small.scroll .popup-body::-webkit-scrollbar-thumb {
  border-radius: 5px;
  background: #949494;
}
.popup.popup-small.scroll .popup-body::-webkit-scrollbar-track {
  border-radius: 5px;
  background: #d1d1d1;
}
.popup.popup-small.scroll .popup-body::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(left, #8391A6, #536175);
}
.popup.popup-small.scroll.host .popup-body {
  padding: 50px 25px 10px 25px;
}
@media (min-width: 576px) {
  .popup.popup-small .popup-dialog {
    max-width: 300px;
    width: 100%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
}
.popup.popup-small .popup-footer.m-0 .message-error {
  margin-top: 0;
}
.popup.popup-big .popup-header, .popup.popup-big .popup-body, .popup.popup-big .popup-footer {
  padding: 25px 80px;
}
@media (max-width: 700px) {
  .popup.popup-big .popup-header, .popup.popup-big .popup-body, .popup.popup-big .popup-footer {
    padding: 10px 20px;
  }
}
@media (min-width: 576px) {
  .popup.popup-big .popup-dialog {
    max-width: 780px;
  }
}
.popup-header .icon-close-small, .popup-header .icon-close-middle, .popup-header .icon-close-big {
  right: -45px;
  position: absolute;
  right: -33px;
  top: -5px;
}
@media (max-width: 900px) {
  .popup-header .icon-close-small, .popup-header .icon-close-middle, .popup-header .icon-close-big {
    width: 20px;
    height: 20px;
    right: -25px;
    background-size: cover;
  }
}
.popup-header.blue, .popup-header.light-blue {
  background-color: #009fe0;
  text-align: center;
}
.popup-header.blue .popup-header-title, .popup-header.light-blue .popup-header-title {
  color: #ffffff;
}
.popup-header.light-blue {
  background-color: #009fdf;
}
.popup-header.red {
  background-color: #eb1c24;
}
.popup-header-title {
  font-size: 16px;
  line-height: 18px;
  font-family: "Roboto Regular";
}
.popup-content {
  position: relative;
  width: 100%;
  pointer-events: auto;
  background-color: #fff;
  outline: 0;
  box-sizing: border-box;
}
.popup-overlay {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1040;
  background-color: #000;
  opacity: 0.7;
}
.popup .icon-close-middle {
  position: absolute;
  right: -33px;
  top: -5px;
}
.popup .icon-close-big {
  position: absolute;
  right: -40px;
  top: -6px;
}
.popup .custom-title {
  font-size: 20px;
}
.popup .container__col-xs-6 {
  margin-bottom: 0;
}
.popup .description-text {
  font-size: 14px;
  line-height: 16px;
  font-family: "Roboto Regular";
  color: #b6b5b5;
}
.popup .custom-control {
  min-height: 0;
}`;const Qr=({popupType:n,children:e,customClass:o})=>c.createElement(c.Fragment,null,c.createElement("div",{className:S(On.popup,{[On[`popup-${n}`]]:!0},On[o||""])},c.createElement("div",{className:S(On["popup-dialog"])},c.createElement("div",{className:S(On["popup-content"])},e))),c.createElement("div",{className:S(On["popup-overlay"])}));var Co=`@charset "UTF-8";
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
@font-face {
  font-family: "icomoon";
  src: url("__VITE_ASSET__86322399__");
  src: url("__VITE_ASSET__86322399__") format("embedded-opentype"), url("__VITE_ASSET__434e2b1b__") format("truetype"), url("__VITE_ASSET__8b06532b__") format("woff"), url("__VITE_ASSET__9d46154f__") format("svg");
  font-weight: normal;
  font-style: normal;
}
.icon-close {
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  display: inline-block;
  cursor: pointer;
}
.icon-close:after {
  content: "\uE915";
  color: #dcdcdc;
}
.icon-close.white:after {
  color: #ffffff;
}
.icon-close.gray:after {
  color: #cdcdcd;
}
.icon-close-small {
  top: -10px;
}
.icon-close-small.error {
  background-position: 0 -77px;
}
.icon-close-middle {
  font-size: 28px;
}
.icon-close-big {
  font-size: 40px;
}
.icon-close-position-small {
  right: -45px;
  position: absolute;
  right: -33px;
  top: -5px;
}
.icon-close-position-middle {
  position: absolute;
  right: -33px;
  top: -5px;
}
.icon-close-position-big {
  position: absolute;
  right: -40px;
  top: -6px;
}
.icon-close-custom {
  position: absolute;
  left: 4px;
  top: 4px;
  font-size: 14px;
}
.switch-hide .icon-action-custom {
  position: absolute;
  right: -20px;
}
.switch-active .icon-close-custom {
  left: -15px;
}
.switch-active .icon-action-custom {
  right: 0;
  z-index: 9;
  position: absolute;
  font-size: 22px;
}
.switch-active .icon-action-custom:after {
  color: #7fb345;
}`;const Jr=({iconType:n,iconPosition:e,onClick:o,customStyle:i})=>c.createElement("span",{className:S(Co["icon-close"],{[Co[`icon-close-${n}`]]:!0},Co[e||""],Co[i||""]),onClick:o});var Yx=`.hr {
  display: block;
  width: 100%;
  height: 1px;
  background-color: #e0e0e0;
}`;const Xr=()=>c.createElement("span",{className:S(Yx.hr)});var To=`/* Colors */
/* Fonts */
.content-hr {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA+YAAAACAQMAAADvk1RtAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAZQTFRFAAAAJ5+V7tkYtAAAAAJ0Uk5TAP9bkSK1AAAAEUlEQVR4nGNgGFBQ/3/gwA8AaHR7/Unc72MAAAAASUVORK5CYII=") no-repeat 50%;
  background-size: contain;
  margin-bottom: 10px;
}
.content-hr-title {
  padding-right: 10px;
  font-size: 12px;
  color: #28a095;
  font-family: "Roboto Regular";
  line-height: 21px;
}
.content-hr-title-blue {
  color: #0072aa;
}
.content-hr-blue {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA+cAAAABAQMAAACGxU39AAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAZQTFRFAHGpAAAAUbYmPwAAAAJ0Uk5T/wDltzBKAAAADUlEQVR4nGNgGEDABAAAgAADwjFv5AAAAABJRU5ErkJggg==") no-repeat 50%;
  background-size: contain;
}`;const Qx=Z(n=>({background:{backgroundColor:n.palette.background.default}})),Jx=({hrTitle:n,hrColor:e,hrTitleColor:o})=>{const i=Qx();return c.createElement("div",{className:S(To["content-hr"],{[To[`content-hr-${e}`]]:e})},c.createElement("span",{className:S(To["content-hr-title"],i.background,{[To[`content-hr-title-${o}`]]:o})},n))};var Ut=`@charset "UTF-8";
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
/* Colors */
/* Fonts */
.m-0 {
  margin: 0 !important;
}
.mb-0 {
  margin-bottom: 0 !important;
}
.mb-1 {
  margin-bottom: 10px;
}
.mb-2 {
  margin-bottom: 20px;
}
.mr-2 {
  margin-right: 20px;
}
.mr-4 {
  margin-right: 40px;
}
.ml-1 {
  margin-left: 10px;
}
.ml-2 {
  margin-left: 20px;
}
.mt-03 {
  margin-top: 3px !important;
}
.mt-05 {
  margin-top: 5px !important;
}
.mt-1 {
  margin-top: 10px;
}
.mt-2 {
  margin-top: 20px;
}
.p-1 {
  padding: 10px;
}
.p-2 {
  padding: 20px;
}
.pt-24 {
  padding-top: 24px;
}
.pt-25 {
  padding-top: 25px;
}
.pb-25 {
  padding-bottom: 25px;
}
.p-0 {
  padding: 0 !important;
}
.pr-0 {
  padding-right: 0 !important;
}
.pr-05 {
  padding-right: 5px !important;
}
.pr-08 {
  padding-right: 8px !important;
}
.pr-09 {
  padding-right: 9px !important;
}
.pr-2 {
  padding-right: 20px !important;
}
.pr-23 {
  padding-right: 23px !important;
}
.pr-24 {
  padding-right: 24px !important;
}
.pl-05 {
  padding-left: 5px !important;
}
.pl-2 {
  padding-left: 20px !important;
}
.pl-22 {
  padding-left: 22px !important;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.img-responsive {
  max-width: 100%;
  height: auto;
}
.text-left {
  text-align-last: left;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.w-100 {
  width: 100%;
}
.f-r {
  float: right;
}
.red-decorater {
  color: #e00b3d;
}
.blue-background-decorator {
  background-color: #29d1d4;
}
.red-background-decorator {
  background-color: #e00b3d;
}
.loading-animation {
  -webkit-animation: spinner 1s infinite linear;
  top: 20% !important;
}
@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate3d(0, 0, 1, 0deg);
    -ms-transform: rotate3d(0, 0, 1, 0deg);
    -o-transform: rotate3d(0, 0, 1, 0deg);
    transform: rotate3d(0, 0, 1, 0deg);
  }
  50% {
    -webkit-transform: rotate3d(0, 0, 1, 180deg);
    -ms-transform: rotate3d(0, 0, 1, 180deg);
    -o-transform: rotate3d(0, 0, 1, 180deg);
    transform: rotate3d(0, 0, 1, 180deg);
  }
  100% {
    -webkit-transform: rotate3d(0, 0, 1, 360deg);
    -ms-transform: rotate3d(0, 0, 1, 360deg);
    -o-transform: rotate3d(0, 0, 1, 360deg);
    transform: rotate3d(0, 0, 1, 360deg);
  }
}
.half-opacity {
  opacity: 0.5;
}
.border-right {
  border-right: 2px solid #dcdcdc;
}
.content-icon {
  display: inline-block;
  width: 30px;
  height: 30px;
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size: 25px;
  position: relative;
}
.content-icon-green, .content-icon-red, .content-icon-orange {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-size: 25px;
  cursor: pointer;
  display: block;
  z-index: 99;
}
.content-icon-green {
  background-color: #7fb345;
}
.content-icon-red {
  background-color: #e00b3d;
}
.content-icon-orange {
  background-color: #ff7200;
}
.content-icon-add:after {
  content: "\uE921";
  color: #ffffff;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.content-icon-add.gray:after {
  color: #606060;
}
.content-icon-add.white:after {
  color: #ffffff;
}
.content-icon-update:after {
  content: "\uE911";
  color: #ffffff;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.content-icon-update.gray:after {
  color: #606060;
}
.content-icon-update.white:after {
  color: #ffffff;
}
.content-icon-delete:after {
  content: "\uE910";
  color: #ffffff;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.content-icon-delete.gray:after {
  color: #606060;
}
.content-icon-delete.white:after {
  color: #ffffff;
}
.content-icon-button {
  display: inline-block;
  position: absolute;
  right: 6px;
  width: 21px;
  height: 20px;
  top: 50%;
  transform: translateY(-50%);
}
.content-icon-popup-wrapper {
  position: absolute;
  right: 100px;
  bottom: -25px;
}
@media (max-width: 576px) {
  .content-icon-popup-wrapper {
    width: 40px;
    height: 40px;
    background-size: 20px;
    bottom: -15px;
    right: 20px;
  }
}`;const Vt=({iconContentType:n,iconContentColor:e,loading:o,onClick:i,customClass:a})=>c.createElement("span",{className:S(Ut["content-icon"],{[Ut[`content-icon-${n}`]]:!0},Ut[e?`content-icon-${e}`:""],Ut[o?"loading-animation":""],Ut[a||""]),style:o?{top:"20%"}:{},onClick:i});var jt=`@charset "UTF-8";
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
.info {
  display: inline-block;
  top: 8px;
  right: 8px;
  width: 16px;
  height: 16px;
  font-size: 15px;
  vertical-align: middle;
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.info-state:after {
  content: "\uE912";
  color: #dcdcdc;
}
.info-state.green:after {
  color: #43b02a;
}
.info-question:after {
  content: "\uE922";
  color: #dcdcdc;
}
.info-question.green:after {
  color: #43b02a;
}
.info-text {
  color: #242f3a;
  font-size: 12px;
  line-height: 14px;
  font-family: "Roboto Regular";
  display: inline-block;
  vertical-align: middle;
  margin-left: 3px;
}
.info.white:after {
  color: #ffffff;
}
.info.gray:after {
  color: #a7a7a7;
}
.info-icon-position {
  position: absolute;
}`;const Xx=({iconText:n,iconName:e=null,iconColor:o=null,iconPosition:i=null})=>{const a=S(jt.info,{[jt[`info-${e}`]]:!0},jt[i||""],jt[o||""]);return c.createElement(c.Fragment,null,e&&c.createElement("span",{className:a}),n&&c.createElement("span",{className:S(jt["info-text"])},n))};var Fi=`@charset "UTF-8";
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
@font-face {
  font-family: "icomoon";
  src: url("__VITE_ASSET__86322399__");
  src: url("__VITE_ASSET__86322399__") format("embedded-opentype"), url("__VITE_ASSET__434e2b1b__") format("truetype"), url("__VITE_ASSET__8b06532b__") format("woff"), url("__VITE_ASSET__9d46154f__") format("svg");
  font-weight: normal;
  font-style: normal;
}
.icons-toggle-arrow {
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  display: inline-block;
  font-size: 28px;
  vertical-align: middle;
  cursor: pointer;
  right: 0;
  top: 13px;
}
.icons-toggle-arrow:after {
  content: "\uE90F";
  color: #dcdcdc;
}
.icons-toggle-position-right {
  position: absolute;
  top: 11px;
  right: 3px;
}
.icons-toggle-position-multiselect {
  position: absolute;
  top: 1px;
  right: 3px;
}
.icons-toggle-position-multiselect-table {
  position: absolute;
  top: -1px;
  right: 2px;
  font-size: 22px;
}
.icons-toggle-rotate {
  transform: rotate(180deg);
}`;const Zr=({iconType:n,iconPosition:e,rotate:o,onClick:i,...a})=>{const r=S({[Fi[`icons-toggle-${n}`]]:!0},Fi[e||""],{[Fi["icons-toggle-rotate"]]:!!o});return c.createElement("span",{className:r,onClick:i,...a})},Zx=Z(n=>({checkbox:{marginRight:n.spacing(1),padding:0},container:{alignItems:"center",display:"flex"}})),ct=c.forwardRef(({children:n,checkboxSelected:e},o)=>{const i=Zx();return c.createElement("div",{className:i.container,ref:o},!Fn(e)&&c.createElement(tr,{checked:e,className:i.checkbox,color:"primary",size:"small"}),c.createElement(St,{variant:"body2"},n))});var qt;(function(n){n.compact="compact",n.small="small"})(qt||(qt={}));const n3=Z(n=>({compact:{fontSize:"x-small",padding:n.spacing(.75)},input:{fontSize:n.typography.body1.fontSize},noLabelInput:{padding:n.spacing(1.25)},small:{fontSize:"small",padding:n.spacing(.75)},transparent:{backgroundColor:"transparent"}})),nc=({label:n,position:e,children:o})=>{const i=!n&&{style:{marginTop:0}};return c.createElement(or,{...i,position:e},o)},ie=c.forwardRef(({StartAdornment:n,EndAdornment:e,label:o,error:i,ariaLabel:a,transparent:r=!1,size:s,...l},p)=>{const d=n3(),m=b=>q(s,b);return c.createElement(od,{InputProps:{className:S({[d.transparent]:r}),disableUnderline:!0,endAdornment:e&&c.createElement(nc,{label:o,position:"end"},c.createElement(e,null)),startAdornment:n&&c.createElement(nc,{label:o,position:"start"},c.createElement(n,null))},error:!Fn(i),helperText:i,inputProps:{"aria-label":a,className:S(d.input,{[d.noLabelInput]:!o&&id(m(qt.compact)),[d.small]:m(qt.small),[d.compact]:m(qt.compact)})},label:o,ref:p,size:"small",variant:"filled",...l})}),ec=Z(n=>({input:{"&:after":{borderBottom:0},"&:before":{borderBottom:0},"&:hover:before":{borderBottom:0},'&[class*="MuiFilledInput-root"]':{paddingTop:n.spacing(2)},paddingTop:n.spacing(1)},inputEndAdornment:{paddingBottom:"19px"},inputLabel:{"&&":{fontSize:n.typography.body1.fontSize,maxWidth:"72%",overflow:"hidden",textOverflow:"ellipsis",transform:"translate(12px, 14px) scale(1)",whiteSpace:"nowrap"}},inputLabelShrink:{"&&":{maxWidth:"90%"}},loadingIndicator:{textAlign:"center"},options:{alignItems:"center",display:"grid",gridAutoFlow:"column",gridGap:n.spacing(1)}})),e3=()=>{const n=ec();return c.createElement("div",{className:n.loadingIndicator},c.createElement(mo,{size:20}))},t3=({options:n,label:e,placeholder:o="",loading:i=!1,onTextChange:a=()=>{},endAdornment:r=void 0,inputValue:s,displayOptionThumbnail:l=!1,required:p=!1,error:d,...m})=>{const b=ec(),f=(x,y)=>{const g=["id","name"];return q(ir(g,x),ir(g,y))};return c.createElement(ad,{classes:{groupLabel:b.inputLabel,inputRoot:b.input},getOptionLabel:x=>x.name,getOptionSelected:f,loading:i,loadingText:c.createElement(e3,null),options:n,renderInput:x=>c.createElement(ie,{...x,InputLabelProps:{classes:{marginDense:b.inputLabel,shrink:b.inputLabelShrink}},InputProps:{...x.InputProps,endAdornment:c.createElement(c.Fragment,null,r&&c.createElement(or,{classes:{root:b.inputEndAdornment},position:"end"},r),x.InputProps.endAdornment)},error:d,inputProps:{...x.inputProps,"aria-label":e},label:e,placeholder:o,required:p,value:s||"",onChange:a}),renderOption:x=>c.createElement("div",{className:b.options},l&&c.createElement("img",{alt:x.name,height:20,src:x.url,width:20}),c.createElement(ct,null,x.name)),size:"small",...m})},o3=Z(n=>({checkbox:{marginRight:n.spacing(1),padding:0},deleteIcon:{height:n.spacing(1.5),width:n.spacing(1.5)},tag:{fontSize:n.typography.caption.fontSize,height:n.spacing(1.75)}})),Pi=n=>{const e=o3(),o=(i,a)=>i.map((r,s)=>c.createElement(ar,{classes:{deleteIcon:e.deleteIcon,root:e.tag},key:r.id,label:r.name,size:"small",...a({index:s})}));return c.createElement(t3,{disableCloseOnSelect:!0,multiple:!0,getLimitTagsText:i=>c.createElement(ct,null,`+${i}`),renderOption:(i,{selected:a})=>c.createElement(ct,{checkboxSelected:a},i.name),renderTags:o,...n})},tc=()=>{const[n]=c.useState(Ge.CancelToken.source());return n};var Li;(function(n){n.error="error",n.info="info",n.success="success",n.warning="warning"})(Li||(Li={}));var Sn=Li;const i3=()=>{const[n,e]=c.useState(),[o,i]=c.useState(Sn.error),a=()=>{e(void 0)},r=({message:l,severity:p})=>{e(l),i(p)};return{confirmMessage:a,message:n,severity:o,showMessage:r,showMessages:({messages:l,severity:p})=>{const d=Object.keys(l),m=d.map(b=>`${b}: ${l[b]}`,[]);r({message:c.createElement("div",{style:{display:"block"}},m.map((b,f)=>c.createElement("p",{key:d[f],style:{margin:0}},b))),severity:p})}}},a3=Z({alertIcon:{paddingTop:"10px"},closeIcon:{fontSize:20,opacity:.9},message:{display:"flex",flexDirection:"column",justifyContent:"center"}}),r3=({message:n,open:e,onClose:o,severity:i})=>{const a=a3();return c.createElement(rd,{anchorOrigin:{horizontal:"center",vertical:"bottom"},autoHideDuration:6e3,open:e,onClose:o},c.createElement(cd,{action:[c.createElement(Je,{color:"inherit",key:"close",onClick:o},c.createElement(sd,{className:a.closeIcon}))],classes:{icon:a.alertIcon,message:a.message},severity:i,variant:"filled"},n))},oc=()=>{},c3={showMessage:oc,showMessages:oc},ic=c.createContext(c3),s3=n=>e=>{const{message:o,severity:i,showMessage:a,showMessages:r,confirmMessage:s}=i3(),l=o!==void 0;return c.createElement(ic.Provider,{value:{showMessage:a,showMessages:r}},c.createElement(n,{...e}),c.createElement(r3,{message:o,open:l,severity:i,onClose:s}))},ae=()=>c.useContext(ic),l3=ld("API Request"),fn=({request:n,decoder:e,getErrorMessage:o,defaultFailureMessage:i="Oops, something went wrong"})=>{const{token:a,cancel:r}=tc(),{showMessage:s}=ae(),[l,p]=c.useState(!1);c.useEffect(()=>()=>r(),[]);const d=b=>{const f=dd(md(i,["response","data","message"]),o)(b);s({message:f,severity:Sn.error})};return{sendRequest:b=>(p(!0),n(a)(b).then(f=>e?e.decodePromise(f):f).catch(f=>{throw l3.error(f),pd([[Ge.isCancel,rr],[rr,d]])(f),f}).finally(()=>p(!1))),sending:l}},Io={"Content-Type":"application/x-www-form-urlencoded"},ce=n=>e=>Ge.get(e,{cancelToken:n}).then(({data:o})=>o),p3=n=>({endpoint:e,data:o})=>Ge.patch(e,o,{cancelToken:n,headers:Io}).then(({data:i})=>i),d3=n=>({endpoint:e,data:o})=>Ge.post(e,o,{cancelToken:n,headers:Io}).then(({data:i})=>i),m3=n=>({endpoint:e,data:o})=>Ge.put(e,o,{cancelToken:n,headers:Io}).then(({data:i})=>i),b3=n=>e=>Ge.delete(e,{cancelToken:n,headers:Io}).then(({data:o})=>o),ac=({maxPage:n,page:e,loading:o,action:i})=>{const a=c.useRef(null);return c.useCallback(s=>{if(a.current&&a.current.disconnect(),o){a.current=null;return}a.current=new IntersectionObserver(([l])=>{l.isIntersecting&&e<n&&i()}),s&&a.current&&a.current.observe(s)},[n,e,o])},x3=(n,e)=>({initialPage:i=1,getEndpoint:a,field:r,search:s,...l})=>{const[p,d]=c.useState([]),[m,b]=c.useState(!1),[f,x]=c.useState(""),[y,g]=c.useState(1),[u,A]=c.useState(i),w=de(),{sendRequest:k,sending:T}=fn({request:ce}),I=({endpoint:U,loadMore:Q=!1})=>{k(U).then(({result:$,meta:sn})=>{d((Q?p:[]).concat($));const Cn=Xe("total",sn)||1,Vn=Xe("limit",sn)||1;A(Math.ceil(Cn/Vn))})},C=ac({action:()=>g(y+1),loading:T,maxPage:u,page:y}),N=U=>bo(U)?s:{...s||{},regex:{fields:[r],value:U}},H=c.useRef(bd(U=>{y===i&&I({endpoint:a({page:i,search:N(U)})}),g(1)},500)),G=U=>{H.current(U.target.value),x(U.target.value)},R=()=>{b(!0)},O=()=>{b(!1)},B=(U,{selected:Q})=>{const $=q(xd(p))(U),sn=$?{ref:C}:{};return c.createElement("div",{style:{width:"100%"}},c.createElement("div",null,e?c.createElement(ct,{checkboxSelected:Q,...sn},U.name):c.createElement(St,{variant:"body2",...sn},U.name)),$&&y>1&&T&&c.createElement("div",{style:{textAlign:"center",width:"100%"}},c.createElement(mo,{size:w.spacing(2.5)})))};return c.useEffect(()=>{if(!m){x(""),d([]),g(i);return}I({endpoint:a({page:y,search:N(f)}),loadMore:y>1})},[m,y]),c.createElement(n,{filterOptions:U=>U,loading:T,options:p,renderOption:B,onClose:O,onOpen:R,onTextChange:G,...l})},f3=x3(Pi,!0),g3=n=>c.createElement(ie,{StartAdornment:()=>c.createElement(fd,null),...n}),y3=Z(n=>({compact:{fontSize:"x-small",padding:n.spacing(.5)},input:{fontSize:n.typography.body1.fontSize},noLabelInput:{padding:n.spacing(1.25)}})),$i=({options:n,onChange:e,selectedOptionId:o,label:i,error:a,fullWidth:r,ariaLabel:s,inputProps:l,compact:p=!1,...d})=>{const m=y3(),b=x=>n.find(Ct("id",x)),f=x=>{Fn(x.target.value)||e(x)};return c.createElement(gd,{error:!Fn(a),fullWidth:r,size:"small",variant:"filled"},i&&c.createElement(yd,null,i),c.createElement(ud,{disableUnderline:!0,displayEmpty:!0,fullWidth:r,inputProps:{"aria-label":s,className:S(m.input,{[m.noLabelInput]:!i&&!p,[m.compact]:p}),...l},renderValue:x=>{var y;return(y=b(x))==null?void 0:y.name},value:o,onChange:f,...d},n.filter(({id:x})=>x!=="").map(({id:x,name:y,color:g,type:u})=>{const A=`${x}-${y}`;return u==="header"?[c.createElement(hd,{key:A},y),c.createElement(cr,{key:`${A}-divider`})]:c.createElement(sr,{key:A,style:{backgroundColor:g},value:x},c.createElement(ct,null,y))})),a&&c.createElement(wd,null,a))},u3="Reset",h3=Z(n=>({button:{fontSize:n.typography.caption.fontSize}})),rc=({icon:n,options:e,title:o,onChange:i,value:a,onReset:r,popperPlacement:s="bottom-start"})=>{const l=de(),p=h3(),{t:d}=Tt(),[m,b]=c.useState(),f=Boolean(m),x=A=>{(A==null?void 0:A.type)!=="mousedown"&&b(void 0)},y=A=>{if(f){x();return}b(A.currentTarget)},g=A=>li(lr(Ct("id",A)),Boolean)(a),u=A=>{const{id:w}=A,k=g(w)?pr(Ct("id",w),a):[...a,A];i(k)};return c.createElement(_d,{onClickAway:x},c.createElement("div",null,c.createElement(Rn,{ariaLabel:o,title:o,onClick:y},n),c.createElement(kd,{anchorEl:m,open:f,placement:s,style:{zIndex:l.zIndex.tooltip}},c.createElement(xo,null,!Fn(r)&&c.createElement(si,{fullWidth:!0,className:p.button,color:"primary",size:"small",startIcon:c.createElement(Ed,null),onClick:r},d(u3)),e.map(A=>{const{id:w,name:k}=A;return c.createElement(sr,{key:w,value:k,onClick:()=>u(A)},c.createElement(ct,{checkboxSelected:g(w)},k))})))))};var w3="/centreon/assets/centreon.78223ad2.png",Mi=`.logo {
  padding: 10px 12px;
  box-sizing: border-box;
  height: 51px;
}
.logo a {
  display: block;
}
.logo-image {
  max-width: 100%;
  height: auto;
}`;class _3 extends c.Component{render(){const{customClass:e,onClick:o}=this.props;return c.createElement("div",{className:S(Mi.logo,Mi[e||""]),onClick:o},c.createElement("span",null,c.createElement("img",{alt:"",className:S(Mi["logo-image"]),height:"57",src:w3,width:"254"})))}}var k3="/centreon/assets/centreon-logo-mini.9fd093fe.svg",Oi=`.logo-mini {
  padding: 16px 11px;
  box-sizing: border-box;
  height: 51px;
}`;class E3 extends c.Component{render(){const{customClass:e,onClick:o}=this.props;return c.createElement("div",{className:S(Oi["logo-mini"],Oi[e||""]),onClick:o},c.createElement("span",null,c.createElement("img",{alt:"",className:S(Oi["logo-mini-image"]),height:"21",src:k3,width:"23"})))}}var cc=`/* Colors */
/* Fonts */
.message-info {
  font-family: "Roboto Regular";
  display: block;
  font-size: 13px;
  line-height: 17px;
}
.message-info.red {
  color: #e00b3d;
  font-size: 14px;
}`;const A3=({messageInfo:n,text:e})=>c.createElement("span",{className:S(cc["message-info"],cc[n||""])},e),v3=n=>(n.width===void 0&&(n.width=n.right-n.left),n.height===void 0&&(n.height=n.bottom-n.top),n);class sc extends c.Component{constructor(){super(...arguments);h(this,"state",{isInViewport:null});h(this,"getContainer",()=>window);h(this,"isIn",()=>{const e=this.node;if(!e)return this.state;const o=v3(this.roundRectDown(e.getBoundingClientRect())),i={bottom:window.innerHeight||document.documentElement.clientHeight,left:0,right:window.innerWidth||document.documentElement.clientWidth,top:0},a={bottom:i.bottom-o.bottom,left:i.left-o.left,offsetHeight:e.offsetHeight,right:i.right-o.right,top:i.top-o.top},s=o.height>0&&o.width>0&&o.top>=i.top&&o.left>=i.left&&o.bottom<=i.bottom&&o.right<=i.right;let{state:l}=this;return(this.state.isInViewport!==s||this.state.rectBox.top!==a.top||a.bottom!==this.state.rectBox.bottom)&&(l={rectBox:a},this.setState(l),this.props.onChange&&this.props.onChange(s)),l})}roundRectDown(e){return{bottom:Math.floor(e.bottom),left:Math.floor(e.left),right:Math.floor(e.right),top:Math.floor(e.top)}}render(){return this.props.children instanceof Function?this.props.children({rectBox:this.state.rectBox}):c.Children.only(this.props.children)}}h(sc,"propTypes",{children:ee.oneOfType([ee.element,ee.func]),onChange:ee.func});var nn=`@charset "UTF-8";
/* Colors */
/* Fonts */
/* Colors */
/* Fonts */
.m-0 {
  margin: 0 !important;
}
.mb-0 {
  margin-bottom: 0 !important;
}
.mb-1 {
  margin-bottom: 10px;
}
.mb-2 {
  margin-bottom: 20px;
}
.mr-2 {
  margin-right: 20px;
}
.mr-4 {
  margin-right: 40px;
}
.ml-1 {
  margin-left: 10px;
}
.ml-2 {
  margin-left: 20px;
}
.mt-03 {
  margin-top: 3px !important;
}
.mt-05 {
  margin-top: 5px !important;
}
.mt-1 {
  margin-top: 10px;
}
.mt-2 {
  margin-top: 20px;
}
.p-1 {
  padding: 10px;
}
.p-2 {
  padding: 20px;
}
.pt-24 {
  padding-top: 24px;
}
.pt-25 {
  padding-top: 25px;
}
.pb-25 {
  padding-bottom: 25px;
}
.p-0 {
  padding: 0 !important;
}
.pr-0 {
  padding-right: 0 !important;
}
.pr-05 {
  padding-right: 5px !important;
}
.pr-08 {
  padding-right: 8px !important;
}
.pr-09 {
  padding-right: 9px !important;
}
.pr-2 {
  padding-right: 20px !important;
}
.pr-23 {
  padding-right: 23px !important;
}
.pr-24 {
  padding-right: 24px !important;
}
.pl-05 {
  padding-left: 5px !important;
}
.pl-2 {
  padding-left: 20px !important;
}
.pl-22 {
  padding-left: 22px !important;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.img-responsive {
  max-width: 100%;
  height: auto;
}
.text-left {
  text-align-last: left;
}
.text-right {
  text-align: right;
}
.text-center {
  text-align: center;
}
.w-100 {
  width: 100%;
}
.f-r {
  float: right;
}
.red-decorater {
  color: #e00b3d;
}
.blue-background-decorator {
  background-color: #29d1d4;
}
.red-background-decorator {
  background-color: #e00b3d;
}
.loading-animation {
  -webkit-animation: spinner 1s infinite linear;
  top: 20% !important;
}
@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate3d(0, 0, 1, 0deg);
    -ms-transform: rotate3d(0, 0, 1, 0deg);
    -o-transform: rotate3d(0, 0, 1, 0deg);
    transform: rotate3d(0, 0, 1, 0deg);
  }
  50% {
    -webkit-transform: rotate3d(0, 0, 1, 180deg);
    -ms-transform: rotate3d(0, 0, 1, 180deg);
    -o-transform: rotate3d(0, 0, 1, 180deg);
    transform: rotate3d(0, 0, 1, 180deg);
  }
  100% {
    -webkit-transform: rotate3d(0, 0, 1, 360deg);
    -ms-transform: rotate3d(0, 0, 1, 360deg);
    -o-transform: rotate3d(0, 0, 1, 360deg);
    transform: rotate3d(0, 0, 1, 360deg);
  }
}
.half-opacity {
  opacity: 0.5;
}
.border-right {
  border-right: 2px solid #dcdcdc;
}
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
@font-face {
  font-family: "icomoon";
  src: url("__VITE_ASSET__86322399__");
  src: url("__VITE_ASSET__86322399__") format("embedded-opentype"), url("__VITE_ASSET__434e2b1b__") format("truetype"), url("__VITE_ASSET__8b06532b__") format("woff"), url("__VITE_ASSET__9d46154f__") format("svg");
  font-weight: normal;
  font-style: normal;
}
.menu-items {
  border-top: 1px solid #ffffff;
  padding-bottom: 30px;
}
.menu-item {
  position: relative;
}
.menu-item-link {
  border-bottom: 1px solid #ffffff;
  text-align: left;
  display: block;
  position: relative;
  cursor: pointer;
}
.menu-item-name {
  font-family: "Roboto Regular";
  font-size: 0.84rem;
  vertical-align: middle;
  position: absolute;
  left: 41px;
  transform: translateY(-50%);
  top: 50%;
  color: #242f3b;
}
.menu-item .iconmoon {
  display: block;
  padding: 7px 12px;
  background-color: transparent;
}
.menu-item .iconmoon:before {
  font-size: 24px;
}
.menu-item .iconmoon.icon-home:before {
  content: "\uE904";
  color: #009f98;
}
.menu-item .iconmoon.icon-home:hover:before {
  content: "\uE904";
  color: #ffffff;
}
.menu-item .iconmoon.icon-monitoring:before {
  content: "\uE907";
  color: #88bd23;
}
.menu-item .iconmoon.icon-monitoring:hover:before {
  content: "\uE907";
  color: #ffffff;
}
.menu-item .iconmoon.icon-reporting:before {
  content: "\uE909";
  color: #ffa200;
}
.menu-item .iconmoon.icon-reporting:hover:before {
  content: "\uE909";
  color: #ffffff;
}
.menu-item .iconmoon.icon-configuration:before {
  content: "\uE902";
  color: #009fe9;
}
.menu-item .iconmoon.icon-configuration:hover:before {
  content: "\uE902";
  color: #ffffff;
}
.menu-item .iconmoon.icon-administration:before {
  content: "\uE900";
  color: #2b2d84;
}
.menu-item .iconmoon.icon-administration:hover:before {
  content: "\uE900";
  color: #ffffff;
}
.menu-item.active .menu-item-name {
  color: #ffffff;
}
.menu-item.active .iconmoon:before {
  color: #ffffff;
}
.menu-item.active-2B9E93 .menu-item-link {
  background-color: #00a499;
}
.menu-item.active-2B9E93 .menu-item-name {
  color: #ffffff;
}
.menu-item.active-2B9E93 .iconmoon:before {
  color: #ffffff;
}
.menu-item.active-85B446 .menu-item-link {
  background-color: #84bd00;
}
.menu-item.active-85B446 .menu-item-name {
  color: #ffffff;
}
.menu-item.active-85B446 .iconmoon:before {
  color: #ffffff;
}
.menu-item.active-E4932C .menu-item-link {
  background-color: #ff9913;
}
.menu-item.active-E4932C .menu-item-name {
  color: #ffffff;
}
.menu-item.active-E4932C .iconmoon:before {
  color: #ffffff;
}
.menu-item.active-17387B .menu-item-link {
  background-color: #10069f;
}
.menu-item.active-17387B .menu-item-name {
  color: #ffffff;
}
.menu-item.active-17387B .iconmoon:before {
  color: #ffffff;
}
.menu-item.active-319ED5 .menu-item-link {
  background-color: #009fdf;
}
.menu-item.active-319ED5 .menu-item-name {
  color: #ffffff;
}
.menu-item.active-319ED5 .iconmoon:before {
  color: #ffffff;
}
.menu-item:hover .collapsed-items {
  display: block !important;
}
.menu-item.color-2B9E93 .menu-item-link:hover {
  background-color: #007f77;
}
.menu-item.color-85B446 .menu-item-link:hover {
  background-color: #597f00;
}
.menu-item.color-E4932C .menu-item-link:hover {
  background-color: #c67000;
}
.menu-item.color-17387B .menu-item-link:hover {
  background-color: #20357e;
}
.menu-item.color-319ED5 .menu-item-link:hover {
  background-color: #005a7f;
}
.menu-item-link:hover .menu-item-name {
  color: #ffffff;
}
.menu-item-link:hover .collapsed-items {
  display: block !important;
}
.menu-item-link:hover .iconmoon:before {
  color: #ffffff;
}
.menu .collapse.collapsed-items {
  display: none;
  position: absolute;
  top: 0;
  left: 100%;
  min-width: 155px;
  width: 100%;
  -webkit-box-shadow: 3px 3px 3px 0 rgba(70, 75, 82, 0.5);
  box-shadow: 3px 3px 3px 0 rgba(70, 75, 82, 0.5);
  box-sizing: border-box;
  z-index: 99;
}
.menu .collapse.collapsed-items.border-2B9E93 {
  border-left: 5px solid #00a499;
}
.menu .collapse.collapsed-items.border-85B446 {
  border-left: 5px solid #84bd00;
}
.menu .collapse.collapsed-items.border-E4932C {
  border-left: 5px solid #ff9913;
}
.menu .collapse.collapsed-items.border-17387B {
  border-left: 5px solid #10069f;
}
.menu .collapse.collapsed-items.border-319ED5 {
  border-left: 5px solid #009fdf;
}
.menu .collapse.collapsed-items.towards-up {
  top: 0;
  bottom: auto;
}
.menu .collapse.collapsed-items.towards-down {
  top: auto;
  bottom: 0;
}
.menu .collapse .collapsed-item {
  position: relative;
}
.menu .collapse .collapsed-item-level-link {
  border-bottom: 1px solid #f6f6f6;
  text-align: left;
  font-size: 0.7rem;
  color: #242f3b;
  font-family: "Roboto Regular";
  padding: 11px 10px 11px 28px;
  display: block;
  letter-spacing: -0.4px;
  background-color: transparent;
  cursor: pointer;
}
.menu .collapse .collapsed-item-level-link:hover {
  color: #ffffff;
  background-image: none !important;
}
.menu .collapse .collapsed-item-level-link:hover.color-2B9E93 {
  background-color: #007f77 !important;
}
.menu .collapse .collapsed-item-level-link:hover.color-85B446 {
  background-color: #597f00 !important;
}
.menu .collapse .collapsed-item-level-link:hover.color-E4932C {
  background-color: #c67000 !important;
}
.menu .collapse .collapsed-item-level-link:hover.color-17387B {
  background-color: #20357e !important;
}
.menu .collapse .collapsed-item-level-link:hover.color-319ED5 {
  background-color: #005a7f !important;
}
.menu .collapse .collapsed-item > .collapsed-item-level-link {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAB4CAMAAADxGOMCAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAEJQTFRFAAAAy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLQ5TTrwAAABZ0Uk5TADsq+owBQfGeAzLrrwck374PGtPMFV5TAM4AAAB3SURBVHic7ZRJDoAgDEVlEBAQkOH+VxWH2Jq4YGvSt2j+T0jL6k3TMAxlLuQMTWmzQLPOr9BCTBu0XGr7LPgZXoBXv4/i7xAEQfwajgIT6spKdM9JbY9stexzNi50XzpzunDxMefob0euqZT0uHOrFam3gXgH2AHDHAOdvO9eCAAAAABJRU5ErkJggg==");
  background-repeat: no-repeat;
  background-position: 12px 9px;
  background-color: #f6f6f6;
}
.menu .collapse .collapsed-item > .collapsed-item-level-link.img-none {
  background-image: none !important;
}
.menu .collapse .collapsed-item:hover .collapsed-level-items {
  display: block !important;
}
.menu .collapse .collapsed-item.active-2B9E93 > .collapsed-item-level-link {
  background-color: #00a499;
  background-image: none;
  color: #ffffff;
}
.menu .collapse .collapsed-item.active-85B446 > .collapsed-item-level-link {
  background-color: #84bd00;
  background-image: none;
  color: #ffffff;
}
.menu .collapse .collapsed-item.active-E4932C > .collapsed-item-level-link {
  background-color: #ff9913;
  background-image: none;
  color: #ffffff;
}
.menu .collapse .collapsed-item.active-17387B > .collapsed-item-level-link {
  background-color: #10069f;
  background-image: none;
  color: #ffffff;
}
.menu .collapse .collapsed-item.active-319ED5 > .collapsed-item-level-link {
  background-color: #009fdf;
  background-image: none;
  color: #ffffff;
}
.menu .collapse .collapsed-level-items {
  display: none;
  position: absolute;
  top: 0;
  left: 100%;
  min-width: 155px;
  width: 100%;
  -webkit-box-shadow: 3px 3px 3px 0 rgba(70, 75, 82, 0.5);
  box-shadow: 3px 3px 3px 0 rgba(70, 75, 82, 0.5);
  background-color: #ffffff;
  z-index: 99;
  overflow-y: auto;
}
.menu .collapse .collapsed-level-item {
  position: relative;
}
.menu .collapse .collapsed-level-item-link {
  border-bottom: 1px solid #f6f6f6;
  text-align: left;
  font-size: 0.7rem;
  color: #242f3b;
  font-family: "Roboto Regular";
  padding: 11px 10px 11px 28px;
  display: block;
  letter-spacing: -0.4px;
  cursor: pointer;
  background-color: transparent;
}
.menu .collapse .collapsed-level-item.active-2B9E93 .collapsed-item-level-link {
  background-color: #00a499;
  color: #ffffff;
}
.menu .collapse .collapsed-level-item.active-85B446 .collapsed-item-level-link {
  background-color: #84bd00;
  color: #ffffff;
}
.menu .collapse .collapsed-level-item.active-E4932C .collapsed-item-level-link {
  background-color: #ff9913;
  color: #ffffff;
}
.menu .collapse .collapsed-level-item.active-17387B .collapsed-item-level-link {
  background-color: #10069f;
  color: #ffffff;
}
.menu .collapse .collapsed-level-item.active-319ED5 .collapsed-item-level-link {
  background-color: #009fdf;
  color: #ffffff;
}
.menu .collapse .collapsed-level-title {
  background-color: #ffffff;
  display: block;
  position: relative;
  margin: 0;
  text-align: center;
}
.menu .collapse .collapsed-level-title:before {
  content: " ";
  height: 1px;
  width: 100%;
  background-color: #009fdf;
  position: absolute;
  display: block;
  top: 10px;
  left: 0;
  right: 0;
}
.menu .collapse .collapsed-level-title span {
  font-family: "Roboto Regular";
  color: #009fdf;
  background: #ffffff;
  font-variant: all-petite-caps;
  font-size: 0.7rem;
  position: relative;
  line-height: 15px;
  max-width: 115px;
  display: inline-block;
  padding: 0 3px 3px;
}
.menu-small .menu-item-name {
  display: none;
}
.menu-big .menu-item.active .collapsed-items {
  display: block !important;
  position: static !important;
  max-height: 446px;
  overflow-y: auto;
  overflow-x: hidden;
}
.menu-big .menu-item.active .collapsed-item:hover .collapsed-level-items {
  display: none !important;
}
.menu-big .menu-item.active .collapsed-item.active .collapsed-level-items {
  display: block !important;
}
.menu-big .menu-item.active .collapsed-items, .menu-big .menu-item.active .collapsed-level-items {
  position: static !important;
  box-shadow: none !important;
}
.menu-big .collapsed-item.active > .collapsed-item-level-link {
  color: #242f3b;
  background-color: #f6f6f6;
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAB4CAMAAADxGOMCAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAEJQTFRFAAAAy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLQ5TTrwAAABZ0Uk5TADsq+owBQfGeAzLrrwck374PGtPMFV5TAM4AAAB3SURBVHic7ZRJDoAgDEVlEBAQkOH+VxWH2Jq4YGvSt2j+T0jL6k3TMAxlLuQMTWmzQLPOr9BCTBu0XGr7LPgZXoBXv4/i7xAEQfwajgIT6spKdM9JbY9stexzNi50XzpzunDxMefob0euqZT0uHOrFam3gXgH2AHDHAOdvO9eCAAAAABJRU5ErkJggg==");
  background-repeat: no-repeat;
  background-position: 10px -93px;
}
.menu-big .collapsed-item.active > .collapsed-item-level-link:hover {
  background-image: none !important;
  color: #ffffff !important;
}`;const Bi=Ad(vd)(()=>({textDecoration:"none"}));class z3 extends c.Component{constructor(){super(...arguments);h(this,"state",{activeSecondLevel:null,doubleClickedLevel:null,hrefOfIframe:!1,navigatedPageId:!1});h(this,"watchHrefChange",e=>{e.detail.href.match(/p=/)&&this.setState({hrefOfIframe:e.detail.href})});h(this,"getUrlFromEntry",e=>{const o=e.page+(e.options!==null?e.options:"");return e.is_react?e.url:`/main.php?p=${o}`});h(this,"activateSecondLevel",e=>{const{activeSecondLevel:o}=this.state;this.setState({activeSecondLevel:o===e?!0:e})});h(this,"getActiveTopLevelIndex",e=>{const{navigationData:o}=this.props;let i=-1;for(let a=0;a<o.length;a++)!isNaN(e)&&String(e).charAt(0)===o[a].page&&(i=a);return i});h(this,"onNavigate",(e,o)=>{const{onNavigate:i}=this.props;this.setState({hrefOfIframe:!1,navigatedPageId:e}),i(e,o)});h(this,"areSamePage",(e,o,i)=>i?!isNaN(e)&&String(e).substring(0,i)===o.page:!isNaN(e)&&e===o.page)}componentDidMount(){window.addEventListener("react.href.update",this.watchHrefChange,!1)}componentWillUnmount(){window.removeEventListener("react.href.update",this.watchHrefChange)}render(){const{navigationData:e,sidebarActive:o,reactRoutes:i}=this.props,{activeSecondLevel:a,doubleClickedLevel:r,navigatedPageId:s,hrefOfIframe:l}=this.state;let p="";const{pathname:d,search:m}=window.location;s&&!l?p=s:l?l.match(/p=/)?(p=l.split("p=")[1],p&&(p=p.split("&")[0])):p=i[l]||l:m.match(/p=/)?p=m.split("p=")[1].split("&")[0]:p=i[d]||d;const b=this.getActiveTopLevelIndex(p);return c.createElement("ul",{className:S(nn.menu,nn["menu-items"],nn["list-unstyled"],nn[o?"menu-big":"menu-small"])},e.map((f,x)=>{const y=f.toggled||this.areSamePage(p,f,1);return c.createElement("li",{className:S(nn["menu-item"],nn[`color-${f.color}`],{[nn.active]:y,[nn[`active-${f.color}`]]:y}),key:`firstLevel-${f.page}`},c.createElement("span",{className:S(nn["menu-item-link"])},c.createElement(Bi,{className:S(nn.iconmoon,nn[`icon-${f.icon}`]),component:pi,to:this.getUrlFromEntry(f),onClick:g=>{r?this.setState({doubleClickedLevel:null,hrefOfIframe:!1}):g.preventDefault()},onDoubleClick:g=>{const u=g.target;this.setState({doubleClickedLevel:f,hrefOfIframe:!1},()=>{u.click()})}},c.createElement("span",{className:S(nn["menu-item-name"])},f.label))),c.createElement("ul",{className:S(nn.collapse,nn["collapsed-items"],nn["list-unstyled"],nn[`border-${f.color}`],nn[b!==-1&&x>b&&o&&e[b].children.length>=5?"towards-down":"towards-up"])},f.children.map(g=>{const u=a===g.page||!a&&this.areSamePage(p,g,3),A=g.toggled||this.areSamePage(p,g,3);return c.createElement("li",{className:S(nn["collapsed-item"],{[nn.active]:u,[nn[`active-${f.color}`]]:A}),key:`secondLevel-${g.page}`},c.createElement(Bi,{className:S(nn["collapsed-item-level-link"],nn[`color-${f.color}`],{[nn["img-none"]]:g.groups.length<1}),component:pi,to:this.getUrlFromEntry(g),onClick:w=>{g.groups.length>0?(w.preventDefault(),this.activateSecondLevel(g.page)):this.setState({hrefOfIframe:!1,navigatedPageId:g.page})}},g.label),c.createElement(sc,{active:!0},({rectBox:w})=>{let k={};return w&&w.bottom<1&&(k={height:w.offsetHeight+w.bottom}),c.createElement("ul",{className:S(nn["collapsed-level-items"],nn["list-unstyled"]),style:k},g.groups.map(T=>c.createElement(c.Fragment,{key:`thirdLevelFragment-${T.label}`},g.groups.length>1?c.createElement("span",{className:S(nn["collapsed-level-title"])},c.createElement("span",null,T.label)):null,T.children.map(I=>{const C=I.toggled||this.areSamePage(p,I);return c.createElement("li",{className:S(nn["collapsed-level-item"],{[nn.active]:C,[nn[`active-${f.color}`]]:C}),key:`thirdLevel-${I.page}`},c.createElement(Bi,{className:S(nn["collapsed-item-level-link"],nn[`color-${f.color}`]),component:pi,to:this.getUrlFromEntry(I),onClick:()=>{this.setState({hrefOfIframe:!1,navigatedPageId:I.page})}},I.label))}))))}))})))}))}}var lc=`/* Colors */
/* Fonts */
.search-live label {
  font-size: 13px;
  line-height: 14px;
  color: #4e4d4d;
  font-family: "Roboto Regular";
  display: block;
  margin-bottom: 10px;
  font-weight: bold;
}
.search-live input {
  width: 100%;
  height: 40px;
  border-radius: 2px;
  background-color: #ffffff;
  border: 1px solid #cdcdcd;
  outline: none;
  padding: 10px 15px;
  box-sizing: border-box;
  margin-bottom: 10px;
}
@media (min-width: 768px) {
  .search-live input {
    max-width: 250px;
  }
}`,C5=`/* Colors */
/* Fonts */
.search-live label {
  font-size: 14px;
  line-height: 14px;
  color: #4e4d4d;
  font-family: "Roboto Regular";
  display: block;
  margin-bottom: 10px;
}
.search-live input {
  width: 100%;
  height: 40px;
  border-radius: 2px;
  background-color: #ffffff;
  border: 1px solid #cdcdcd;
  outline: none;
  padding: 10px 15px;
  box-sizing: border-box;
  margin-bottom: 10px;
}
@media (min-width: 768px) {
  .search-live input {
    max-width: 250px;
  }
}
.search-live-custom {
  display: inline-block;
  position: relative;
}`;class S3 extends c.Component{constructor(){super(...arguments);h(this,"onChange",e=>{const{onChange:o,filterKey:i}=this.props;o(e.target.value,i)})}render(){const{label:e,value:o,icon:i}=this.props;return c.createElement("div",{className:S(lc["search-live"],lc[i?"custom":""])},e&&c.createElement("label",null,c.createElement("b",null,e)),c.createElement("input",{type:"text",value:o,onChange:this.onChange.bind(this)}),i?c.createElement(jx,{buttonActionType:"delete",buttonColor:"green",buttonIconType:"arrow-right",iconColor:"white"}):null)}}var C3="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAxEAAAFfCAMAAAAGdLFhAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAASBQTFRFBZ+VF6adIqqiJqyjJaujHqmgEaSaDKKYRrixotvY////5fX08/v6+Pz8+f39B6CWCaGXCKCW/v//4vTz7vj46Pb1Bp+VC6GYHaifCaCXv+bk2fHv3PLw1e/tuOTh7Pj32vHvDqKZ3fLx6/f3DqOZGKadseHeI6uiyero/f7+qN7a0e3rGqeeFqWcruDdH6mgEqSa6fb29/z84/TzEKOa/P7+xOjm9vv7z+3rueTiweflGaeept3Z6vf2w+jm8vr5yuvp+/39D6OZIKmgIqqhFaWcG6ifsuHf0O3rwujlr+Dd7/n42/HwC6GX1u/uHKifDaKYvebjIKmhJKujueTh0e3sxunnt+PhGaed9Pv6x+rnE6Sb4fTyCqGX4PPyJKuikO9n6QAACAVJREFUeJzt3Wt/FNUdwPHEED3ESIhACC22xYrBcrFWamup2FLtTYv23trb+38X3Vw+5J9kZi9zzuycZb7fBzyA3Tm7+zm//DfJMrO2BgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAalp/ZePS5qtDPwqowmtp4vLhH2nohwLDm4Swdex1TcCLHg5ta4KROxPExBuaYMzOBzFxRROM1s7FIDTBiDWMCE0wXldbgtAE49Q2IjTBKO1OC0ITjM6ZEXGlqYk3NcF4nPlB02Trv9E4KDTBWMQRcS0dfppjWxOM17kRsbamCUbt3Ig4+TtNMFIXR8SRaU3sDPl4oV9NI+LkHzTB+LSMiCOaYHxaR8TJv2qCUZk2Io5Mtv51TTAacUTcaP4xkiYYj5kj4ogmGIs5RsTJ7dqbuLq8hwv92ptrRBzRBCMw74g4ubEmeMnNPyKOb64JXmoLjYiTe6QbuU3cTPn2M541tFlwRBzfZ0oTu3Pc/9bkdt/69u0sb33HBw7pweIj4uRu7U3Mc+fG+y4spZs5zx0u6jIiju/Y3sTMe5bpYeK7xgRldRwRJ/dtbmLGLi0YxOFitzKePZzXeUQc37u5iakHaj5zYGffMyQoKGdEnBygqYl0Z74lS/C+iYLyRsTxIVK6tsAunXVaqIW9rQiKiV+vv995ZzU0kfbabrxfuoit9E7XBw7nFBgRx8c530S623rTd4sX4Td1FBJHxPW8Nx/nmkgHrTe8V7yIS1mPHF5I74V9lft2/Myv3X7QerR0PyyZ4XStyxuZjxyOFRwRx8fbnmOXxiIePOwsPVIEpaVwvuMCP8OMgaX3W28Vi8hYTBGU1uOI2Eo/bL2VIqhUjyPig/bDKYJKFR8R4f8RTQlMEVSqxxFxZdrpCxRBlQYaEYqgUgONCEVQp6FGhCKo01AjQhFUabARoQiqNNiIUAQ1ijt4e6kjQhHUKP2otxGxNeNwiqA+A44IRVChM5/JW+6IUAT1GXJEKIL6DDkiFEF1Bh0RiqA6g44IRVCb4iMinNNvnvOCK4K6DDsiFEFlBh4RiqAy6cNBR4QiqMvjgUeEIqjLZlpwB0+38IhQBHW5dLqFBxkRiqAuG5fjFs4/12scETtz3UUR1OSVuIev5Tax+IhQBHVZP3tBk7wmOowIRVCXvfOX+MlposOIUASVuXhhxM5NdBkRiqAydxquA9exiS4jQhHUpvHquV2a6DQiFEF1mi8o/eHCTXT7PIgiqE7LNdZvLNZEtxGhCCqUSjQRr026yN0UQX1285voOCIUQZ2u5jbRcUQoglrlNdF1RCiCeuU00XVEKIKatX4/8cGMJjqPCEVQt522Jq5PbaLziFAEtevSxNkRcXWR5RRB9RZvovuIUASrYMEmMkaEIlgNCzWRMSIUwaqYv4mcEaEIVsfenE3kjAhFsFLamtgOTWSNCEWwYmY3kW5njAhFsHJmNJE3IhTBCpraRN6IUAQrabL1f9zWRNaIUAQrarL1rzQ2EYPocAYPRbCq2prIGhGKYIXNaKLbSZ4UwQqb2kS62eWIimCltTfR7dyYimDVtTXR5buIs0Xk+EgRDGayAX9SaESspZ+eHuLj7k6DUAQDmDSxXSSItfSz5vdg3aWDss8V5nGuic4XnWj5dXhOEU+KPlGY06SJ11/00PkqLA/LF7HIqUCgoMPvaC///PDPdzIO8knZIJ4WuOQqdPTq5kY6uJv1Rbn026b0uNSTg0Gkt4oGYUSw6tKngoCg3BsnQfBSSOkXBXL4Ze4F6qEW61mf4Hjh2dDPA0rZXf/VZ1k2Px/6KQAAAAAAAAAAAAAAAAAArLLd/fz///xw5ip7v85fZX0JrwZjtzPZab+5dz/Hb393uF2nLzO5we/zVrn/dPYqkCsVOpXSJ1M3a6lVvkhpb2kvDWNU8JysU5KYeQ3U+X0pCXpU9CTFX7QlUXSV97xxoje7Zc/a3TYlyk2IaatAtuLnsW/8kVPxVSRBP/5Q+uo/95r26l7pVZ4rgn70cIW4WxdX+ar8Kl8v/7ViDNIfi+/Vuw2rvFt8la+W/1oxBul58b36p4ZVSpwn/+wqf17+a8UYpNunmyxH2KufNaxyv/QqrvBOP2IRD7pLH89dRM4qjxRBz2IRsz+q136YBYrIWEUR9E0RECkCIkVApAiIFAGRIiBSBESKgEgRECkCIkVApAiIFAGRIiBSBESKgEgRECkCIkVApAiIFAGRIiBSBESKgEgRECkCIkVApAiIFAGRIiBSBESKgEgRECkCIkVApAiIFAGRIiBSBESKgEgRECkCIkVApAiIFAGRIiBSBESKgEgRECkCIkVApAiIFAGRIiBSBESKgEgREMUicnw0dxE5/qIIehaK2HrU3WkQs4rIWeU0CEXQk/TXrcLS+w2r/K34KgfLf60Yg/T34nv184ZVUvFVniz/tWIM9kvv1TdTwyr/KF/EraW/VIxD6b2amopYS/8su8o3jatAvtJvaNKzpazyr2W/ToxGul50q7Z88U7/XsYqkO/rkl++W7fq7lJWgQJS/J1Eb1s1pedLWAUK+E9K/y2xU59O3ap7KX1ZYpVvBEHvjj4icTnL4REeL2UV31SzBM82L21kOXiyM3uVO/uZq/zvwdX+XwsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAlfV/c8TZtW60cz8AAAAASUVORK5CYII=",T3="/centreon/assets/slider-default-image-widget.71422777.png",Kn=`@charset "UTF-8";
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
.content-slider {
  position: relative;
  width: 100%;
  margin: 0 auto;
  height: 400px;
  overflow: hidden;
  white-space: nowrap;
}
.content-slider-items {
  position: relative;
  height: 100%;
  width: 100%;
  transition: transform ease-out 0.45s;
}
.content-slider-item {
  display: inline-block;
  height: 100%;
  width: 100%;
  background-size: 100% 100%;
  background-repeat: no-repeat;
}
.content-slider-wrapper {
  position: relative;
  max-width: 900px;
  margin: 0 auto;
  box-shadow: 1px 1px 5px 2px rgba(70, 75, 82, 0.2);
}
.content-slider-prev, .content-slider-next {
  position: absolute;
  top: 0;
  bottom: 0;
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 12%;
  color: #fff;
  text-align: center;
  z-index: 99;
  cursor: pointer;
}
.content-slider-prev-icon, .content-slider-next-icon {
  font-size: 40px;
  cursor: pointer;
}
@media (max-width: 576px) {
  .content-slider-prev-icon, .content-slider-next-icon {
    width: 18px;
    height: 29px;
    background-size: cover !important;
  }
}
.content-slider-prev {
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  left: 0;
}
.content-slider-prev-icon:after {
  content: "\uE917";
  color: #dcdcdc;
}
.content-slider-prev-icon.white:after {
  color: #ffffff;
}
.content-slider-prev-icon.gray:after {
  color: #606060;
}
.content-slider-next {
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  right: 0;
  transform: rotate(180deg);
}
.content-slider-next-icon:after {
  content: "\uE917";
  color: #dcdcdc;
}
.content-slider-next-icon.white:after {
  color: #ffffff;
}
.content-slider-next-icon.gray:after {
  color: #606060;
}
.content-slider-indicators {
  position: absolute;
  bottom: 20px;
  z-index: 99;
  text-align: center;
  left: 0;
  right: 0;
}
@media (max-width: 576px) {
  .content-slider-indicators {
    bottom: 7px;
  }
}
.content-slider-indicators span {
  width: 12px;
  height: 12px;
  background-color: #606060;
  display: inline-block;
  border-radius: 50%;
  cursor: pointer;
  margin-right: 15px;
}
.content-slider-indicators span.active {
  background-color: #ffffff;
  box-shadow: 1px 1px 4px 2px rgba(70, 75, 82, 0.5);
}`;const pc=({image:n,isActive:e})=>c.createElement("div",{alt:"Slider image",className:S(Kn["content-slider-item"],Kn[e?"active-slide":""]),style:{backgroundImage:`url(${n})`}}),I3=({goToPrevSlide:n,iconColor:e})=>c.createElement("span",{className:S(Kn["content-slider-prev"]),onClick:n},c.createElement("span",{className:S(Kn["content-slider-prev-icon"],Kn[e||""])})),N3=({goToNextSlide:n,iconColor:e})=>c.createElement("span",{className:S(Kn["content-slider-next"]),onClick:n},c.createElement("span",{className:S(Kn["content-slider-next-icon"],Kn[e||""])})),R3=({images:n,currentIndex:e,handleDotClick:o})=>c.createElement("div",{className:S(Kn["content-slider-indicators"])},n.map((i,a)=>c.createElement("span",{className:S(Kn[a===e?"active":"dot"]),"data-index":a,key:a,onClick:o})));class G3 extends c.Component{constructor(e){super(e);h(this,"goToPrevSlide",()=>{const{currentIndex:e}=this.state;e!==0&&this.setState(o=>({currentIndex:o.currentIndex-1,translateValue:o.translateValue+this.slideWidth()}))});h(this,"goToNextSlide",()=>{const{currentIndex:e}=this.state,{images:o}=this.props;if(e===o.length-1)return this.setState({currentIndex:0,translateValue:0});this.setState(i=>({currentIndex:i.currentIndex+1,translateValue:i.translateValue-this.slideWidth()}))});h(this,"slideWidth",()=>document.querySelector(".content-slider-wrapper")?document.querySelector(".content-slider-wrapper").clientWidth:780);h(this,"renderSlides",()=>{const{currentIndex:e}=this.state,{type:o,images:i}=this.props,a=i.map((r,s)=>{const l=e===s;return c.createElement(pc,{image:r,isActive:l,key:s})});if(i.length===0){const r=o==="widget"?T3:C3;return[c.createElement(pc,{isActive:!0,image:r,key:0})]}return a});h(this,"handleDotClick",e=>{const{currentIndex:o,translateValue:i}=this.state,a=parseInt(e.target.getAttribute("data-index"));if(a<o)return this.setState({currentIndex:a,translateValue:-a*this.slideWidth()});this.setState({currentIndex:a,translateValue:i+(o-a)*this.slideWidth()})});this.state={currentIndex:0,translateValue:0}}render(){const{currentIndex:e,translateValue:o}=this.state,{images:i,children:a}=this.props;return c.createElement("div",{className:S(Kn["content-slider-wrapper"])},c.createElement("div",{className:S(Kn["content-slider"])},c.createElement("div",{className:S(Kn["content-slider-items"]),style:{transform:`translateX(${o}px)`}},this.renderSlides()),c.createElement("div",{className:S(Kn["content-slider-controls"])},e===0?null:c.createElement(I3,{goToPrevSlide:this.goToPrevSlide,iconColor:"gray"}),i.length<=1?null:c.createElement(N3,{goToNextSlide:this.goToNextSlide,iconColor:"gray"})),c.createElement(R3,{currentIndex:e,handleDotClick:this.handleDotClick,images:i})),a)}}var Wt=`/* Colors */
/* Fonts */
.sidebar {
  min-width: 45px;
  max-width: 45px;
  text-align: center;
  background: #ededed;
  transition: all 0.2s;
  border-right: 1px solid #d1d2d4;
  z-index: 99;
}
.sidebar-inner {
  position: relative;
}
.sidebar.active {
  min-width: 160px;
  max-width: 160px;
}
.sidebar.active .sidebar-toggle-wrap {
  width: 160px;
}
.sidebar.active .sidebar-toggle-icon {
  transform: none;
  background-position: 90%;
}
.sidebar-toggle-wrap {
  background: #ffffff;
  width: 45px;
  height: 30px;
  position: fixed;
  bottom: 0;
  transition: all 0.2s;
}
.sidebar-toggle-icon {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAJCAMAAAA8eE0hAAAAXVBMVEUAAAAvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMjYvMja1PN23AAAAH3RSTlMAOEsJDTdRxnQMhM5qy1cTl8Eieb9DJ6SiElP0TsggMaEZOAAAAEBJREFUeJxjYAACRiZmFiZWEIuNnYOTi5sRyOLh5eMXEBRiYOAUFhEVE5eQBIpJScswiMvKgRQiiSKpRTEBZi4AoksDRoZfJoUAAAAASUVORK5CYII=") no-repeat center center;
  transform: rotateY(180deg);
  height: 30px;
  display: inline-block;
  position: absolute;
  bottom: 0;
  right: 0;
  cursor: pointer;
  width: 100%;
  transition: all 0.2s;
}`;class D3 extends c.Component{constructor(){super(...arguments);h(this,"state",{active:!1});h(this,"toggleNavigation",()=>{const{active:e}=this.state;this.setState({active:!e})})}render(){const{navigationData:e,reactRoutes:o,style:i}=this.props,{active:a}=this.state;return c.createElement("nav",{className:S(Wt.sidebar,Wt[a?"active":"mini"]),id:"sidebar",style:i},c.createElement("div",{className:S(Wt["sidebar-inner"])},a?c.createElement(_3,{onClick:this.toggleNavigation}):c.createElement(E3,{onClick:this.toggleNavigation}),c.createElement(z3,{navigationData:e||[],reactRoutes:o||{},sidebarActive:a}),c.createElement("div",{className:S(Wt["sidebar-toggle-wrap"]),onClick:this.toggleNavigation},c.createElement("span",{className:S(Wt["sidebar-toggle-icon"])}))))}}var Ui=`/* Colors */
/* Fonts */
@font-face {
  font-family: "Roboto Light";
  src: url("__VITE_ASSET__6f6d7b33__") format("woff2"), url("__VITE_ASSET__2f0e40ac__") format("woff"), url("__VITE_ASSET__a6d343d4__") format("truetype");
}
@font-face {
  font-family: "Roboto Regular";
  src: url("__VITE_ASSET__b11b2aeb__") format("woff2"), url("__VITE_ASSET__91658dab__") format("woff"), url("__VITE_ASSET__79e85140__") format("truetype");
}
@font-face {
  font-family: "Roboto Medium";
  src: url("__VITE_ASSET__48afa2e1__") format("woff2"), url("__VITE_ASSET__96cff21a__") format("woff"), url("__VITE_ASSET__b1b55bae__") format("truetype");
}
@font-face {
  font-family: "Roboto Bold";
  src: url("__VITE_ASSET__2adae71b__") format("woff2"), url("__VITE_ASSET__16e6f826__") format("woff"), url("__VITE_ASSET__37f5abe1__") format("truetype");
}
.custom-subtitle {
  margin: 0px 5px;
  font-size: 12px;
  color: #999696;
  font-family: "Roboto Regular";
  cursor: pointer;
}
.custom-subtitle.bam {
  color: #009fdf;
}`;const H3=({label:n,subtitleType:e,customSubtitleStyles:o})=>{const i=S(Ui["custom-subtitle"],Ui[e],Ui[o||""]);return c.createElement("span",{className:i},n)},F3=n=>{const e=c.useRef(),o=c.useRef(0);return q(n,e.current)||(e.current=n,o.current+=1),[o.current]},re=({Component:n,memoProps:e})=>c.useMemo(()=>n,F3(e)),dc=n=>{const e=de();return c.createElement(tr,{color:"primary",size:"small",style:{padding:e.spacing(.5)},...n})};var en;(function(n){n[n.string=0]="string",n[n.component=1]="component"})(en||(en={}));const P3=Z(n=>({root:{"&:last-child":{paddingRight:({compact:e})=>n.spacing(e?0:2)},backgroundColor:({isRowHovered:e,row:o,rowColorConditions:i})=>{if(e)return dr(n.palette.primary.main,.08);const a=i==null?void 0:i.find(({condition:r})=>r(o));return Fn(a)?"unset":a.color},padding:({compact:e})=>n.spacing(0,0,0,e?.5:1.5)}})),No=n=>{const e=P3(n),{children:o}=n;return c.createElement(mr,{classes:{root:e.root},component:"div",...zd(["isRowHovered","row","rowColorConditions","compact"],n)},o)},mc=Z(()=>({cell:{alignItems:"center",alignSelf:"stretch",display:"flex",overflow:"hidden",whiteSpace:"nowrap"},text:{overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}})),L3=({row:n,column:e,listingCheckable:o,isRowSelected:i,isRowHovered:a,rowColorConditions:r})=>{const s=mc({listingCheckable:o}),l={align:"left",className:s.cell,compact:e.compact,isRowHovered:a,row:n,rowColorConditions:r};return{[en.string]:()=>{const{getFormattedString:d,isTruncated:m,getColSpan:b}=e,f=b==null?void 0:b(i),x=(d==null?void 0:d(n))||"",y=f?`auto / span ${f}`:"auto / auto",g=c.createElement(St,{className:s.text,variant:"body2"},x);return c.createElement(No,{style:{gridColumn:y},...l},m&&c.createElement(er,{title:x},g),!m&&g)},[en.component]:()=>{const{getHiddenCondition:d,clickable:m}=e,b=e.Component;return(d==null?void 0:d(i))?null:c.createElement(No,{onClick:x=>{!m||(x.preventDefault(),x.stopPropagation())},...l},c.createElement(b,{isHovered:a,isSelected:i,row:n}))}}[e.type]()},$3=c.memo(L3,(n,e)=>{var Q,$,sn,yn,Cn,Vn,Tn,Y,In,Zn;const{column:o,row:i,isRowHovered:a,isRowSelected:r,rowColorConditions:s}=n,l=o.hasHoverableComponent,p=(Q=o.getRenderComponentOnRowUpdateCondition)==null?void 0:Q.call(o,i),d=($=o.getRenderComponentCondition)==null?void 0:$.call(o,i),m=l&&a,b=(sn=o.getFormattedString)==null?void 0:sn.call(o,i),f=o.isTruncated,x=(yn=o.getColSpan)==null?void 0:yn.call(o,r),y=(Cn=o.getHiddenCondition)==null?void 0:Cn.call(o,r),{column:g,row:u,isRowHovered:A,isRowSelected:w,rowColorConditions:k}=e,T=g.hasHoverableComponent,I=(Vn=g.getRenderComponentOnRowUpdateCondition)==null?void 0:Vn.call(g,u),C=(Tn=g.getRenderComponentCondition)==null?void 0:Tn.call(g,u),N=T&&A,H=(Y=g.getFormattedString)==null?void 0:Y.call(g,u),G=(In=g.getColSpan)==null?void 0:In.call(g,w),R=(Zn=g.getHiddenCondition)==null?void 0:Zn.call(g,w),O=g.isTruncated,B=s==null?void 0:s.map(({condition:En})=>En(i)),U=k==null?void 0:k.map(({condition:En})=>En(u));return d&&C?!1:C===!1?!0:q(m,N)&&q(a,A)&&q(b,H)&&q(x,G)&&q(f,O)&&q(y,R)&&q(p&&i,I&&u)&&q(i,u)&&q(B,U)}),M3=Z(()=>({root:{fontWeight:"bold"}})),bc=({children:n,className:e})=>{const o=M3();return c.createElement(St,{className:e,classes:{root:o.root},variant:"body2"},n)},O3=Z(n=>({content:{alignItems:"center",display:"flex"},dragHandle:({isDragging:e})=>({alignSelf:"flex-start",cursor:e?"grabbing":"grab",display:"flex",marginLeft:-n.spacing(1),outline:"none"})})),xc=c.forwardRef(({column:n,columnConfiguration:e,sortField:o,sortOrder:i,onSort:a,isDragging:r,...s},l)=>{const p=O3({isDragging:r}),d=n.sortField||n.id,m=()=>{const b=Td(q(d,o),q(i,"desc"));a==null||a({sortField:d,sortOrder:b?"asc":"desc"})};return c.createElement("div",{className:p.content,ref:l},(e==null?void 0:e.sortable)&&c.createElement("div",{className:p.dragHandle,...s},c.createElement(Sd,{fontSize:"small"})),n.sortable?c.createElement(Cd,{active:o===d,"aria-label":`Column ${n.label}`,direction:i||"desc",onClick:m},c.createElement(bc,null,n.label)):c.createElement(bc,null,n.label))}),B3=Z(()=>({item:({transform:n,transition:e,isSorting:o})=>({display:"flex",opacity:o?.5:1,transform:Id.Translate.toString(n),transition:e||void 0})})),U3=({column:n,columnConfiguration:e,onSort:o,sortOrder:i,sortField:a})=>{const{id:r}=n,{attributes:s,listeners:l,setNodeRef:p,transition:d,transform:m,isSorting:b}=Nd({id:r}),f=B3({isSorting:b,transform:m,transition:d||void 0}),x=mc({listingCheckable:!0});return c.createElement(gc,{className:S([x.cell,f.item]),component:"div",key:n.id,padding:n.compact?"none":"default",sortDirection:q(a,n.id)?i:!1},c.createElement(xc,{column:n,columnConfiguration:e,ref:p,sortField:a,sortOrder:i,onSort:o,...s,...l}))},fc=28,gc=It(n=>({root:{backgroundColor:n.palette.common.white,height:fc,padding:n.spacing(0,0,0,1.5)}}))(mr),V3=Z(n=>({compactCell:{paddingLeft:n.spacing(.5)},headerLabelDragging:{cursor:"grabbing"},row:{display:"contents"}})),j3=({onSelectAllClick:n,sortOrder:e,sortField:o,selectedRowCount:i,rowCount:a,columns:r,columnConfiguration:s,onSort:l,onSelectColumns:p,checkable:d})=>{const m=V3(),b=Rd(Gd(Ld)),f=Ro({columnConfiguration:s,columns:r}),x=f.map(Xe("id")),[y,g]=c.useState(),u=T=>{g($d(["active","id"],T))},A=()=>{g(void 0)},w=({over:T})=>{if(Fn(T))return;const{id:I}=T,C=br(y,x),N=br(I,x),H=Md(C,N,x);p==null||p(H),g(void 0)},k=T=>lr(Ct("id",T),r);return c.createElement(Dd,{sensors:b,onDragCancel:A,onDragEnd:w,onDragStart:u},c.createElement(Hd,{className:m.row,component:"div"},c.createElement(di,{className:m.row,component:"div"},d&&c.createElement(gc,{component:"div"},c.createElement(dc,{checked:i===a,indeterminate:i>0&&i<a,inputProps:{"aria-label":"Select all"},onChange:n})),c.createElement(Fd,{items:x},f.map(T=>c.createElement(U3,{column:T,columnConfiguration:s,key:T.id,sortField:o,sortOrder:e,onSort:l}))))),c.createElement(Pd,null,y&&c.createElement(xc,{isDragging:!0,column:k(y),columnConfiguration:s})))},q3=c.memo(j3,(n,e)=>q(n.sortOrder,e.sortOrder)&&q(n.sortField,e.sortField)&&q(n.selectedRowCount,e.selectedRowCount)&&q(n.rowCount,e.rowCount)&&q(n.columns,e.columns)&&q(n.checkable,e.checkable)&&q(n.columnConfiguration,e.columnConfiguration)),W3=Z(()=>({row:{cursor:"pointer",display:"contents",width:"100%"}})),K3=c.memo(({children:n,tabIndex:e,onMouseOver:o,onFocus:i,onClick:a})=>{const r=W3();return c.createElement(di,{className:r.row,component:"div",tabIndex:e,onClick:a,onFocus:i,onMouseOver:o},n)},(n,e)=>{const{row:o,rowColorConditions:i}=n,{row:a,rowColorConditions:r}=e,s=i==null?void 0:i.map(({condition:p})=>p(o)),l=r==null?void 0:r.map(({condition:p})=>p(a));return q(n.isHovered,e.isHovered)&&q(n.isSelected,e.isSelected)&&q(n.row,e.row)&&q(n.className,e.className)&&q(s,l)&&q(n.columnIds,e.columnIds)&&q(n.columnConfiguration,e.columnConfiguration)}),Y3=()=>c.createElement(c.Fragment,null,["skeleton1","skeleton2","skeleton3"].map(n=>c.createElement(fo,{animation:"wave",height:20,key:n}))),Q3=({ref:n,onResize:e})=>{c.useEffect(()=>{const o=new Od(e),i=n==null?void 0:n.current;return o.observe(i),()=>{o.unobserve(i)}},[])},yc=n=>{if(li(Bd(void 0,"offsetParent"),Fn)(n))return 0;const e=n;return yc(e.offsetParent)+e.offsetTop},J3="No result found",X3="of",Z3="Rows per page",nf="First page",ef="Last page",tf="Next page",of="Previous page",af="Add columns",rf={toolbar:{height:"32px",minHeight:"auto",overflow:"hidden",paddingLeft:5}},cf=n=>c.createElement(Ud,{component:"div",...n}),sf=c.memo(cf,(n,e)=>q(n.rowsPerPage,e.rowsPerPage)&&q(n.page,e.page)&&q(n.count,e.count)&&q(n.labelRowsPerPage,e.labelRowsPerPage));var lf=It(rf)(sf);const pf=Z(n=>({root:{color:n.palette.text.secondary,flexShrink:0}})),df=({onChangePage:n,page:e,rowsPerPage:o,count:i})=>{const a=pf(),{t:r}=Tt(),s=x=>{n(x,0)},l=x=>{n(x,e-1)},p=x=>{n(x,e+1)},d=Math.ceil(i/o)-1,m=e===0,b=e>=d,f=x=>{n(x,Math.max(0,d))};return c.createElement("div",{className:a.root},c.createElement(Je,{"aria-label":r(nf),disabled:m,onClick:s},c.createElement(Vd,null)),c.createElement(Je,{"aria-label":r(of),disabled:m,onClick:l},c.createElement(jd,null)),c.createElement(Je,{"aria-label":r(tf),disabled:b,onClick:p},c.createElement(qd,null)),c.createElement(Je,{"aria-label":r(ef),disabled:b,onClick:f},c.createElement(Wd,null)))},uc=n=>n.map(({id:e,label:o})=>({id:e,name:o})),mf=({columns:n,columnConfiguration:e,onSelectColumns:o,onResetColumns:i})=>{const{t:a}=Tt(),r=Ro({columnConfiguration:e,columns:n}),s=l=>{o==null||o(l.map(Xe("id")))};return c.createElement(rc,{icon:c.createElement(Kd,null),options:uc(n),popperPlacement:"bottom-end",title:a(af),value:uc(r),onChange:s,onReset:i})},bf=Z(n=>({actions:{padding:n.spacing(1)},container:{alignItems:"center",display:"grid",gridGap:n.spacing(1),gridTemplateColumns:"1fr auto auto",width:"100%"},pagination:{padding:0}})),xf=({actions:n,onPaginate:e,onLimitChange:o,paginated:i,totalRows:a,currentPage:r,limit:s,columns:l,columnConfiguration:p,onResetColumns:d,onSelectColumns:m})=>{const{t:b}=Tt(),f=bf(),x=u=>{o==null||o(u.target.value),e==null||e(0)},y=(u,A)=>{e==null||e(A)},g=({from:u,to:A,count:w})=>`${u}-${A} ${b(X3)} ${w}`;return c.createElement("div",{className:f.container},c.createElement("div",{className:f.actions},n),(p==null?void 0:p.selectedColumnIds)&&c.createElement(mf,{columnConfiguration:p,columns:l,onResetColumns:d,onSelectColumns:m}),i&&c.createElement(lf,{ActionsComponent:df,SelectProps:{native:!0},className:f.pagination,colSpan:3,count:a,labelDisplayedRows:g,labelRowsPerPage:b(Z3),page:r,rowsPerPage:parseInt(s,10),rowsPerPageOptions:[10,20,30,40,50,60,70,80,90,100],onChangePage:y,onChangeRowsPerPage:x}))},Ro=({columnConfiguration:n,columns:e})=>{const o=n==null?void 0:n.selectedColumnIds;return Fn(o)?e:o.map(i=>e.find(Ct("id",i)))},hc=3,ff=Z(n=>({actionBar:{alignItems:"center",display:"flex"},container:{background:"none",display:"flex",flexDirection:"column",height:"100%",width:"100%"},emptyDataCell:{paddingLeft:n.spacing(2)},emptyDataRow:{display:"contents"},loadingIndicator:{height:hc,width:"100%"},paper:{overflow:"auto"},table:{alignItems:"center",display:"grid",position:"relative"},tableBody:{display:"contents",position:"relative"}})),gf={sortable:!1},yf=({limit:n=10,columns:e,columnConfiguration:o=gf,onResetColumns:i,onSelectColumns:a,rows:r=[],currentPage:s=0,totalRows:l=0,checkable:p=!1,rowColorConditions:d=[],loading:m=!1,paginated:b=!0,selectedRows:f=[],sortOrder:x=void 0,sortField:y=void 0,innerScrollDisabled:g=!1,actions:u,disableRowCheckCondition:A=()=>!1,onPaginate:w,onLimitChange:k,onRowClick:T=()=>{},onSelectRows:I=()=>{},onSort:C,getId:N=({id:H})=>H})=>{const{t:H}=Tt(),[G,R]=c.useState(0),[O,B]=c.useState(null),U=c.useRef(),Q=c.useRef(),$=ff(),sn=de();Q3({onResize:()=>{R(yc(U.current))},ref:U});const yn=F=>!!f.find(on=>q(N(on),N(F))),Cn=F=>{if(F.target.checked&&F.target.getAttribute("data-indeterminate")==="false"){I(r);return}I([])},Vn=(F,on)=>{if(F.preventDefault(),F.stopPropagation(),yn(on)){I(f.filter(bn=>!q(N(bn),N(on))));return}I([...f,on])},Tn=F=>{q(O,N(F))||B(N(F))},Y=()=>{B(null)},In=F=>yn(F),Zn=n-Math.min(n,l-s*n),En=()=>{var F;return g?"100%":`calc(100vh - ${G}px - ${(F=Q.current)==null?void 0:F.offsetHeight}px - ${fc}px - ${hc}px - ${sn.spacing(1)}px)`},J=()=>{const F=p?"min-content ":"",on=Ro({columnConfiguration:o,columns:e}).map(({width:bn})=>Fn(bn)?"auto":typeof bn=="number"?`${bn}px`:bn).join(" ");return`${F}${on}`};return c.createElement(c.Fragment,null,m&&r.length>0&&c.createElement(Yd,{className:$.loadingIndicator}),(!m||m&&r.length<1)&&c.createElement("div",{className:$.loadingIndicator}),c.createElement("div",{className:$.container,ref:U},c.createElement("div",{className:$.actionBar,ref:Q},c.createElement(xf,{actions:u,columnConfiguration:o,columns:e,currentPage:s,limit:n,paginated:b,totalRows:l,onLimitChange:k,onPaginate:w,onResetColumns:i,onSelectColumns:a})),c.createElement(xo,{square:!0,className:$.paper,elevation:1,style:{maxHeight:En()}},c.createElement(Qd,{stickyHeader:!0,className:$.table,component:"div",size:"small",style:{gridTemplateColumns:J()}},c.createElement(q3,{checkable:p,columnConfiguration:o,columns:e,rowCount:n-Zn,selectedRowCount:f.length,sortField:y,sortOrder:x,onSelectAllClick:Cn,onSelectColumns:a,onSort:C}),c.createElement(Jd,{className:$.tableBody,component:"div",onMouseLeave:Y},r.map(F=>{const on=In(F),bn=q(O,N(F));return c.createElement(K3,{columnConfiguration:o,columnIds:e.map(Xe("id")),isHovered:bn,isSelected:on,key:N(F),row:F,rowColorConditions:d,tabIndex:-1,onClick:()=>{T(F)},onFocus:()=>Tn(F),onMouseOver:()=>Tn(F)},p&&c.createElement(No,{align:"left",isRowHovered:bn,row:F,rowColorConditions:d,onClick:Nn=>Vn(Nn,F)},c.createElement(dc,{checked:on,disabled:A(F),inputProps:{"aria-label":`Select row ${N(F)}`}})),Ro({columnConfiguration:o,columns:e}).map(Nn=>c.createElement($3,{column:Nn,isRowHovered:bn,isRowSelected:on,key:`${N(F)}-${Nn.id}`,listingCheckable:p,row:F,rowColorConditions:d})))}),r.length<1&&c.createElement(di,{className:$.emptyDataRow,component:"div",tabIndex:-1},c.createElement(No,{align:"center",className:$.emptyDataCell,isRowHovered:!1,style:{gridColumn:`auto / span ${e.length+1}`}},m?c.createElement(Y3,null):H(J3))))))))},uf=({memoProps:n=[],limit:e=10,columns:o,rows:i=[],currentPage:a=0,totalRows:r=0,checkable:s=!1,rowColorConditions:l=[],loading:p=!1,paginated:d=!0,selectedRows:m=[],sortOrder:b=void 0,sortField:f=void 0,innerScrollDisabled:x=!1,...y})=>re({Component:c.createElement(yf,{checkable:s,columns:o,currentPage:a,innerScrollDisabled:x,limit:e,loading:p,paginated:d,rowColorConditions:l,rows:i,selectedRows:m,sortField:f,sortOrder:b,totalRows:r,...y}),memoProps:[...n,o,e,i,a,r,s,p,d,m,b,f,x]}),hf=Xd(()=>({container:{display:"grid",gridTemplateColumns:"1fr auto",gridTemplateRows:"1fr"},content:{gridArea:n=>n?"1 / 1 / 1 / 1":"1 / 1 / 1 / span 2"},panel:{gridArea:"1 / 2",zIndex:4}})),wc=({children:n,panel:e,open:o,fixed:i=!1})=>{const r=hf(!!i&&o);return c.createElement("div",{className:r.container},c.createElement("div",{className:r.content},n),o&&c.createElement("div",{className:r.panel},e))},wf=Z(n=>({filters:{zIndex:4},listing:{height:"100%",marginLeft:n.spacing(2),marginRight:n.spacing(2)},page:{backgroundColor:n.palette.background.default,display:"grid",gridTemplateRows:"auto 1fr",height:"100%",overflow:"hidden"}})),_f=({listing:n,filters:e,panel:o,panelOpen:i=!1,panelFixed:a=!1})=>{const r=wf();return c.createElement("div",{className:r.page},c.createElement("div",{className:r.filters},e),c.createElement(wc,{fixed:a,open:i,panel:o},c.createElement("div",{className:r.listing},n)))},kf=It(n=>({content:{"&$expanded":{margin:n.spacing(1,0)},flexGrow:0,margin:n.spacing(1,0)},expanded:{},focused:{},root:{"&$expanded":{minHeight:"auto"},"&$focused":{backgroundColor:"unset"},justifyContent:"flex-start",minHeight:"auto",padding:n.spacing(0,3,0,2)}}))(xr),Ef=It(n=>({root:{padding:n.spacing(0,.5,1,2)}}))(fr),Af=c.forwardRef(({expandLabel:n,expanded:e=!1,onExpand:o,filters:i,expandableFilters:a},r)=>{const s=!Fn(o);return c.createElement(gr,{square:!0,expanded:s?e:!1},c.createElement(kf,{IconButtonProps:{onClick:o},expandIcon:s&&c.createElement(yr,{"aria-label":n,color:"primary"}),ref:r,style:{cursor:"default"}},i),a&&c.createElement(Ef,null,a))}),vf=({memoProps:n=[],expanded:e,...o})=>re({Component:c.createElement(Af,{expanded:e,...o}),memoProps:[...n,e]}),_c=40;Z(()=>({tab:{minHeight:_c,minWidth:"unset",paddingBottom:0,paddingTop:0}}));const zf=Z(n=>({body:{display:"grid",gridArea:"3 / 1 / 4 / 1",gridTemplateRows:"auto 1fr",height:"100%"},container:{display:"grid",gridTemplate:"auto auto 1fr / 1fr",height:"100%",overflow:"hidden",width:({width:e})=>e},content:{bottom:0,left:0,overflow:"auto",position:"absolute",right:0,top:0},contentContainer:{backgroundColor:n.palette.background.default,position:"relative"},divider:{gridArea:"2 / 1 / 3 / 1"},dragger:{bottom:0,cursor:"ew-resize",position:"absolute",right:({width:e})=>e,top:0,width:5,zIndex:n.zIndex.drawer},header:{alignItems:"center",backgroundColor:({headerBackgroundColor:e})=>e,display:"grid",gridArea:"1 / 1 / 2 / 1",gridTemplateColumns:"1fr auto",padding:n.spacing(1)},tabs:{minHeight:_c}})),kc=c.forwardRef(({header:n,tabs:e=[],selectedTabId:o=0,selectedTab:i,onClose:a,onTabSelect:r=()=>{},labelClose:s="Close",width:l=550,minWidth:p=550,headerBackgroundColor:d,onResize:m},b)=>{const f=zf({headerBackgroundColor:d,width:l}),x=()=>window.innerWidth*.85,y=()=>{const w=x();l>w&&(m==null||m(w))};c.useEffect(()=>(window.addEventListener("resize",y),()=>{window.removeEventListener("resize",y)}),[]);const g=()=>{document.addEventListener("mouseup",u,!0),document.addEventListener("mousemove",A,!0)},u=()=>{document.removeEventListener("mouseup",u,!0),document.removeEventListener("mousemove",A,!0)},A=c.useCallback(w=>{w.preventDefault();const k=x(),T=document.body.clientWidth-w.clientX,I=()=>T<=p?p:T>k?k:T;m==null||m(I())},[]);return c.createElement(ur,{in:!0,direction:"left",timeout:{enter:150,exit:50}},c.createElement(xo,{className:f.container,elevation:2},!Fn(m)&&c.createElement("div",{className:f.dragger,role:"none",onMouseDown:g}),n&&c.createElement(c.Fragment,null,c.createElement("div",{className:f.header},n,a&&c.createElement(Rn,{ariaLabel:s,title:s,onClick:a},c.createElement(Zd,{color:"action"}))),c.createElement(cr,{className:f.divider})),c.createElement("div",{className:f.body},c.createElement(nm,{color:"default",position:"static"},!bo(e)&&c.createElement(em,{className:f.tabs,indicatorColor:"primary",textColor:"primary",value:o,variant:"fullWidth",onChange:r},e.map(w=>w))),c.createElement("div",{className:f.contentContainer,ref:b},c.createElement("div",{className:f.content},i)))))}),Sf=c.forwardRef(({memoProps:n=[],tabs:e,selectedTabId:o,labelClose:i,width:a,minWidth:r,headerBackgroundColor:s,...l},p)=>re({Component:c.createElement(kc,{headerBackgroundColor:s,labelClose:i,minWidth:r,ref:p,selectedTabId:o,tabs:e,width:a,...l}),memoProps:[...n,o,i,a,r,s]}));var Le=`/* Colors */
/* Fonts */
@font-face {
  font-family: "Roboto Light";
  src: url("__VITE_ASSET__6f6d7b33__") format("woff2"), url("__VITE_ASSET__2f0e40ac__") format("woff"), url("__VITE_ASSET__a6d343d4__") format("truetype");
}
@font-face {
  font-family: "Roboto Regular";
  src: url("__VITE_ASSET__b11b2aeb__") format("woff2"), url("__VITE_ASSET__91658dab__") format("woff"), url("__VITE_ASSET__79e85140__") format("truetype");
}
@font-face {
  font-family: "Roboto Medium";
  src: url("__VITE_ASSET__48afa2e1__") format("woff2"), url("__VITE_ASSET__96cff21a__") format("woff"), url("__VITE_ASSET__b1b55bae__") format("truetype");
}
@font-face {
  font-family: "Roboto Bold";
  src: url("__VITE_ASSET__2adae71b__") format("woff2"), url("__VITE_ASSET__16e6f826__") format("woff"), url("__VITE_ASSET__37f5abe1__") format("truetype");
}
.custom-title {
  font-size: 22px;
  margin: 0;
  margin-bottom: 5px;
  color: #06a096;
  font-family: "Roboto Light";
  position: relative;
  cursor: pointer;
  font-weight: bold;
}
.custom-title-styles {
  padding-left: 55px;
  max-width: 190px;
}
.custom-title-styles .custom-title-icon {
  position: absolute;
  left: 0;
}
.custom-title-styles .custom-title-label {
  padding-left: 0 !important;
  min-height: 0 !important;
}
.custom-title-icon {
  width: 51px;
  height: 51px;
  position: absolute;
  left: 0;
  top: 0;
}
.custom-title-icon-puzzle {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADMAAAAzCAMAAAANf8AYAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAnZQTFRFAAAAAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAHGpAjR6tAAAANJ0Uk5TAAx+4v/HVhbU7Ky8oNB1BRyCU5XqFJ9rVCyYljsBDTdtHe/khS2GclhlKZLuzGkaQ44I4/LpBqi3Vw4HtifIf0SEYs38kX2bUJCvKi536/36TP7bPl0biUlcZMPOFwOXlEIgwCbW6Hkz8dEJTrmwNab7LyGcQXbfZ0B49rgkboFNE4eNaNIPKN3zsdWDS3RwPPfw+QTgW8URo6FqpXrGOfUCCkjErSvt2Fq14TKeFecQtD9VRzGpJYpRi8FmNDjaC3vX9ICPmq4Sp5O6siMecdOZesyw1wAAA2hJREFUeJyVlvkjVUEUx4e+kS1C9n2rlxYhxctSSZLCI4o2svasIRVlJ6lIRIv2SNpp16JN+/IfNfdej3m5w+v8MHO+58znzdy5M+c+Qvimpz8LmG0wzYgpZjgHRsYmgOl/MGaYa06IhQnm6YxYwkpyrDFfV8YGFpJjCzv+KHsHRyfnCWUMF8lxhRsXcfcA4OnlPS594Co5C7CQy1hBsch3MbBk6TJB+mG5GPYPgCEPcQ5cQdsgv5V0tlXBIUSJ1UI4NAzh3GkisEZyDNeuAyLXR2EDFdHARi5CYrBpwo/dHABsoU/mHRefwEf0VIlaOmlrMm1TsI2PkO2yO5qKNEt/LrMDO2Wiu3YDcXvS5ZEM7JVPZM7LArJzYmVSUYjmLiHXKg/Yl+r+b9wTai5DiDq/gL61wiKtYBGKp0EEc1fsQMl+NpKC0hkYImyiIyvLoCg/YFKx6uB0zCEcZqVrJV0vKuj1555HQqqgvQ1HjlbX1BLDOpiZ85BapMkn6rGWxzRgq3xCHc/5MUIa0cSo5uKVmttJjrVwkAwcZ5RLNn3+csnfVdnKYU7gJKNOoa0dp6WJg+Eljzh3nOlkZFc8IWfR3UNdJ1VksjzTril3otnjHG3Po8ThQgxgyVnaReQyaiPE+9HbQp/q0mUO4gJrrYVeuSo619qv23MIoRIrGJWEeu7ISXPDKQsD0xuFDWKhvYk+HZiMfkh2K5SQEBzTSg7czht0vDMVOqJ/N7xP71447j8gAzjKZJQPgcYtQA53QlukkG6telOFMnfS+cgDQ1xoMJDgBqOHESP2pZFGQey4zuq7jzX+ExBEMDkbPJWcZ5rzJ5lQQZ6P+2mUecHUyZfQk5yTGGGQV3joZ4Rm0e9RrSOJUE3WSRuMv9U2re9UGWLJa7wRPqu1YRgmb0dbaZ08LNVJO02VeMfujHmLJ22r8f5DalQJPoqx3E+0TjYqaIG4hzEh8PkLvjLTJGBU6BroO4B1myaqrvlGddXrfJRRVdSP784MU4AfYq9MTy9XsluTTOukdF1/duAXm2nCb8I1b33fYdrV4bpWWIFpK6VoPqirZXWWDv9DLOYAYwYT5yATBTMi9KSMdAMq30eS8sKwDgy1Zv0u+p+kV3jD/fHKGYdr7NCfSCBsqFfcfp1NPXSc7n72lC/TX19Iq71TBD92AAAAAElFTkSuQmCC") no-repeat 50%;
}
.custom-title-icon-object {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC4AAAAuCAMAAABgZ9sFAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAH5QTFRFAAAA////////////////FqacBZ+V////DqKZ////////EKOaCaGXE6Sb////CaCXKKyk////H6mgDaKY////ObOr////GaedDKKY////IKmh////////////////HKifB6CWFKWbI6qi////J6yjIKmgEaSaCaGXE6Wb////3uiKUgAAACp0Uk5TAB8bAkLV/xPnCxHi89sH9bYSxOkZoCHQ7Q3CDyAVCcr52r8GuMPh9Nsc9Ah7sAAAAUdJREFUeJztlNtawjAQhFNZVlooSqWAp6qcqu//giaBJLMlwVx45edcleWf6bJZotRZxY3K14iMsnEaM3O24ZYMm22g8YRKZxhlhSuVazDh9qGULdFQGC4M5rmi6QxV01yEe8Od/Uz3LLKZCl1dQPiph+bhhDcNLYOoblemut4I+tHZDY4pFq8uwp+YVBIfhpfeHsOfL8KJ3Rwj+MsAD+FR/HWwKiE80TuteeMNEB7H9WjAAOEJHA0YnsTBAOFXcG/AMRm8CxK4Nby9f0AebYcrhrg1iLfrGQvRQl1T/O+RVlehdj/Qv6Yq2mWqdaJ9A9q6jcDiAc57yviNnllnB3mEYs/krl2aydcyVeZUj6LY+1PV+DL8kM7hqZ35x/8oHtmZQy+LAf/kGqQ3cmc3spdF14H2tiB/A7ay6hubFyvU+Yb4EkWd/Q0u0ydAKvdF0gAAAABJRU5ErkJggg==") no-repeat 50%;
}
.custom-title-icon + .custom-title-label {
  padding-left: 56px;
  min-height: 51px;
  display: inline-block;
}
.custom-title.image {
  padding-left: 65px;
}
.custom-title.image .custom-title-icon {
  left: 0;
}
.custom-title-label {
  text-overflow: ellipsis;
  max-height: 87px;
  overflow: hidden;
}
.custom-title-label.blue {
  color: #0072aa;
}
.custom-title-label.host {
  font-size: 16px;
  line-height: 20px;
  font-family: "Roboto Light";
  color: #009fdf;
  cursor: text;
}
.custom-title-label.bam {
  font-size: 16px;
  line-height: 20px;
  color: #88bd23;
}
.custom-title-label.host {
  font-size: 16px;
  line-height: 20px;
  color: #009fdf;
}
.custom-title-label-container {
  display: flex;
  flex-direction: column;
}`;const Vi=({icon:n,label:e,title:o,titleColor:i,customTitleStyles:a,onClick:r,style:s,labelStyle:l,children:p})=>c.createElement("div",{className:S(Le["custom-title"],a?Le["custom-title-styles"]:""),style:s,onClick:r},n?c.createElement("span",{className:S(Le["custom-title-icon"],{[Le[`custom-title-icon-${n}`]]:!0})}):null,c.createElement("div",{className:Le["custom-title-label-container"]},c.createElement("span",{className:S(Le["custom-title-label"],Le[i||""]),style:l,title:o||e},e),p));var Cf=`/* Colors */
/* Fonts */
.container {
  padding: 20px;
}
.container-gray {
  background-color: #f9f9f9;
}
.container-gray .container__col-md-3,
.container-gray .container__col-md-4,
.container-gray .container__col-sm-6,
.container-gray .container__col-md-9 {
  margin: 0;
}
.container-blue {
  background-color: #29d1d4;
}
.container-red {
  background-color: #e00b3d;
}
.content-wrapper {
  padding: 12px 20px 0 20px;
  box-sizing: border-box;
  margin: 0 auto;
}
@media (max-width: 767px) {
  .content-wrapper {
    padding: 12px;
  }
}
.content-wrap {
  height: 100vh;
  display: flex;
  flex-direction: column;
}
.content-inner {
  flex: 1;
  overflow: auto;
}
.content-overflow {
  flex: 1;
  min-height: 0px;
}`;class ji extends c.Component{render(){const{children:e,style:o}=this.props;return c.createElement("div",{className:S(Cf["content-wrapper"]),style:o},e)}}var Se=`.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}

.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}

.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}

.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}

.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}

.flex-none {
  flex: none !important;
  max-width: none !important;
}

.opacity-1-1 {
  opacity: 1;
}

.opacity-1-2 {
  opacity: 0.5;
}

.opacity-1-3 {
  opacity: 0.3333333333;
}

.opacity-1-4 {
  opacity: 0.25;
}

.opacity-1-5 {
  opacity: 0.2;
}

.opacity-1-6 {
  opacity: 0.1666666667;
}

.opacity-1-7 {
  opacity: 0.1428571429;
}

.opacity-1-8 {
  opacity: 0.125;
}

.opacity-1-9 {
  opacity: 0.1111111111;
}

.opacity-1-10 {
  opacity: 0.1;
}

.opacity-2-1 {
  opacity: 2;
}

.opacity-2-2 {
  opacity: 1;
}

.opacity-2-3 {
  opacity: 0.6666666667;
}

.opacity-2-4 {
  opacity: 0.5;
}

.opacity-2-5 {
  opacity: 0.4;
}

.opacity-2-6 {
  opacity: 0.3333333333;
}

.opacity-2-7 {
  opacity: 0.2857142857;
}

.opacity-2-8 {
  opacity: 0.25;
}

.opacity-2-9 {
  opacity: 0.2222222222;
}

.opacity-2-10 {
  opacity: 0.2;
}

.opacity-3-1 {
  opacity: 3;
}

.opacity-3-2 {
  opacity: 1.5;
}

.opacity-3-3 {
  opacity: 1;
}

.opacity-3-4 {
  opacity: 0.75;
}

.opacity-3-5 {
  opacity: 0.6;
}

.opacity-3-6 {
  opacity: 0.5;
}

.opacity-3-7 {
  opacity: 0.4285714286;
}

.opacity-3-8 {
  opacity: 0.375;
}

.opacity-3-9 {
  opacity: 0.3333333333;
}

.opacity-3-10 {
  opacity: 0.3;
}

.opacity-4-1 {
  opacity: 4;
}

.opacity-4-2 {
  opacity: 2;
}

.opacity-4-3 {
  opacity: 1.3333333333;
}

.opacity-4-4 {
  opacity: 1;
}

.opacity-4-5 {
  opacity: 0.8;
}

.opacity-4-6 {
  opacity: 0.6666666667;
}

.opacity-4-7 {
  opacity: 0.5714285714;
}

.opacity-4-8 {
  opacity: 0.5;
}

.opacity-4-9 {
  opacity: 0.4444444444;
}

.opacity-4-10 {
  opacity: 0.4;
}

.opacity-5-1 {
  opacity: 5;
}

.opacity-5-2 {
  opacity: 2.5;
}

.opacity-5-3 {
  opacity: 1.6666666667;
}

.opacity-5-4 {
  opacity: 1.25;
}

.opacity-5-5 {
  opacity: 1;
}

.opacity-5-6 {
  opacity: 0.8333333333;
}

.opacity-5-7 {
  opacity: 0.7142857143;
}

.opacity-5-8 {
  opacity: 0.625;
}

.opacity-5-9 {
  opacity: 0.5555555556;
}

.opacity-5-10 {
  opacity: 0.5;
}

.opacity-6-1 {
  opacity: 6;
}

.opacity-6-2 {
  opacity: 3;
}

.opacity-6-3 {
  opacity: 2;
}

.opacity-6-4 {
  opacity: 1.5;
}

.opacity-6-5 {
  opacity: 1.2;
}

.opacity-6-6 {
  opacity: 1;
}

.opacity-6-7 {
  opacity: 0.8571428571;
}

.opacity-6-8 {
  opacity: 0.75;
}

.opacity-6-9 {
  opacity: 0.6666666667;
}

.opacity-6-10 {
  opacity: 0.6;
}

.opacity-7-1 {
  opacity: 7;
}

.opacity-7-2 {
  opacity: 3.5;
}

.opacity-7-3 {
  opacity: 2.3333333333;
}

.opacity-7-4 {
  opacity: 1.75;
}

.opacity-7-5 {
  opacity: 1.4;
}

.opacity-7-6 {
  opacity: 1.1666666667;
}

.opacity-7-7 {
  opacity: 1;
}

.opacity-7-8 {
  opacity: 0.875;
}

.opacity-7-9 {
  opacity: 0.7777777778;
}

.opacity-7-10 {
  opacity: 0.7;
}

.opacity-8-1 {
  opacity: 8;
}

.opacity-8-2 {
  opacity: 4;
}

.opacity-8-3 {
  opacity: 2.6666666667;
}

.opacity-8-4 {
  opacity: 2;
}

.opacity-8-5 {
  opacity: 1.6;
}

.opacity-8-6 {
  opacity: 1.3333333333;
}

.opacity-8-7 {
  opacity: 1.1428571429;
}

.opacity-8-8 {
  opacity: 1;
}

.opacity-8-9 {
  opacity: 0.8888888889;
}

.opacity-8-10 {
  opacity: 0.8;
}

.opacity-9-1 {
  opacity: 9;
}

.opacity-9-2 {
  opacity: 4.5;
}

.opacity-9-3 {
  opacity: 3;
}

.opacity-9-4 {
  opacity: 2.25;
}

.opacity-9-5 {
  opacity: 1.8;
}

.opacity-9-6 {
  opacity: 1.5;
}

.opacity-9-7 {
  opacity: 1.2857142857;
}

.opacity-9-8 {
  opacity: 1.125;
}

.opacity-9-9 {
  opacity: 1;
}

.opacity-9-10 {
  opacity: 0.9;
}

.opacity-10-1 {
  opacity: 10;
}

.opacity-10-2 {
  opacity: 5;
}

.opacity-10-3 {
  opacity: 3.3333333333;
}

.opacity-10-4 {
  opacity: 2.5;
}

.opacity-10-5 {
  opacity: 2;
}

.opacity-10-6 {
  opacity: 1.6666666667;
}

.opacity-10-7 {
  opacity: 1.4285714286;
}

.opacity-10-8 {
  opacity: 1.25;
}

.opacity-10-9 {
  opacity: 1.1111111111;
}

.opacity-10-10 {
  opacity: 1;
}

/* Colors */

/* Fonts */

.container {
  padding: 20px;
}

.container-gray {
  background-color: #f9f9f9;
}

.container-gray .container__col-md-3,
.container-gray .container__col-md-4,
.container-gray .container__col-sm-6,
.container-gray .container__col-md-9 {
  margin: 0;
}

.container-blue {
  background-color: #29d1d4;
}

.container-red {
  background-color: #e00b3d;
}

.content-wrapper {
  padding: 12px 20px 0 20px;
  box-sizing: border-box;
  margin: 0 auto;
}

@media (max-width: 767px) {
  .content-wrapper {
    padding: 12px;
  }
}

.content-wrap {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.content-inner {
  flex: 1;
  overflow: auto;
}

.content-overflow {
  flex: 1;
  min-height: 0px;
}

/* Colors */

/* Fonts */

.m-0 {
  margin: 0 !important;
}

.mb-0 {
  margin-bottom: 0 !important;
}

.mb-1 {
  margin-bottom: 10px;
}

.mb-2 {
  margin-bottom: 20px;
}

.mr-2 {
  margin-right: 20px;
}

.mr-4 {
  margin-right: 40px;
}

.ml-1 {
  margin-left: 10px;
}

.ml-2 {
  margin-left: 20px;
}

.mt-03 {
  margin-top: 3px !important;
}

.mt-05 {
  margin-top: 5px !important;
}

.mt-1 {
  margin-top: 10px;
}

.mt-2 {
  margin-top: 20px;
}

.p-1 {
  padding: 10px;
}

.p-2 {
  padding: 20px;
}

.pt-24 {
  padding-top: 24px;
}

.pt-25 {
  padding-top: 25px;
}

.pb-25 {
  padding-bottom: 25px;
}

.p-0 {
  padding: 0 !important;
}

.pr-0 {
  padding-right: 0 !important;
}

.pr-05 {
  padding-right: 5px !important;
}

.pr-08 {
  padding-right: 8px !important;
}

.pr-09 {
  padding-right: 9px !important;
}

.pr-2 {
  padding-right: 20px !important;
}

.pr-23 {
  padding-right: 23px !important;
}

.pr-24 {
  padding-right: 24px !important;
}

.pl-05 {
  padding-left: 5px !important;
}

.pl-2 {
  padding-left: 20px !important;
}

.pl-22 {
  padding-left: 22px !important;
}

.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}

.img-responsive {
  max-width: 100%;
  height: auto;
}

.text-left {
  text-align-last: left;
}

.text-right {
  text-align: right;
}

.text-center {
  text-align: center;
}

.w-100 {
  width: 100%;
}

.f-r {
  float: right;
}

.red-decorater {
  color: #e00b3d;
}

.blue-background-decorator {
  background-color: #29d1d4;
}

.red-background-decorator {
  background-color: #e00b3d;
}

.loading-animation {
  -webkit-animation: spinner 1s infinite linear;
  top: 20% !important;
}

@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate3d(0, 0, 1, 0deg);
    -ms-transform: rotate3d(0, 0, 1, 0deg);
    -o-transform: rotate3d(0, 0, 1, 0deg);
    transform: rotate3d(0, 0, 1, 0deg);
  }
  50% {
    -webkit-transform: rotate3d(0, 0, 1, 180deg);
    -ms-transform: rotate3d(0, 0, 1, 180deg);
    -o-transform: rotate3d(0, 0, 1, 180deg);
    transform: rotate3d(0, 0, 1, 180deg);
  }
  100% {
    -webkit-transform: rotate3d(0, 0, 1, 360deg);
    -ms-transform: rotate3d(0, 0, 1, 360deg);
    -o-transform: rotate3d(0, 0, 1, 360deg);
    transform: rotate3d(0, 0, 1, 360deg);
  }
}

.half-opacity {
  opacity: 0.5;
}

.border-right {
  border-right: 2px solid #dcdcdc;
}

@media screen and (max-width: -1px) {
  .hidden-xs-down {
    display: none !important;
  }
}

.hidden-xs-up {
  display: none !important;
}

@media screen and (max-width: 219px) {
  .hidden-xs-down {
    display: none !important;
  }
}

@media screen and (min-width: 220px) {
  .hidden-xs-up {
    display: none !important;
  }
}

@media screen and (max-width: 639px) {
  .hidden-sm-down {
    display: none !important;
  }
}

@media screen and (min-width: 640px) {
  .hidden-sm-up {
    display: none !important;
  }
}

@media screen and (max-width: 767px) {
  .hidden-md-down {
    display: none !important;
  }
}

@media screen and (min-width: 768px) {
  .hidden-md-up {
    display: none !important;
  }
}

@media screen and (max-width: 991px) {
  .hidden-lg-down {
    display: none !important;
  }
}

@media screen and (min-width: 992px) {
  .hidden-lg-up {
    display: none !important;
  }
}

@media screen and (max-width: 1199px) {
  .hidden-xl-down {
    display: none !important;
  }
}

@media screen and (min-width: 1200px) {
  .hidden-xl-up {
    display: none !important;
  }
}

@media screen and (max-width: 1599px) {
  .hidden-xxl-down {
    display: none !important;
  }
}

@media screen and (min-width: 1600px) {
  .hidden-xxl-up {
    display: none !important;
  }
}

.container--fluid {
  margin: 0;
  max-width: 100%;
}

.container__row {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
  margin-right: -12px;
  margin-left: -12px;
}

.container__col-offset-0 {
  margin-left: 0;
}

.container__col-1 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 8.3333333333%;
  flex: 0 0 8.3333333333%;
  max-width: 8.3333333333%;
  margin-bottom: 10px;
}

.container__col-offset-1 {
  margin-left: 8.3333333333%;
}

.container__col-2 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 16.6666666667%;
  flex: 0 0 16.6666666667%;
  max-width: 16.6666666667%;
  margin-bottom: 10px;
}

.container__col-offset-2 {
  margin-left: 16.6666666667%;
}

.container__col-3 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 25%;
  flex: 0 0 25%;
  max-width: 25%;
  margin-bottom: 10px;
}

.container__col-offset-3 {
  margin-left: 25%;
}

.container__col-4 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 33.3333333333%;
  flex: 0 0 33.3333333333%;
  max-width: 33.3333333333%;
  margin-bottom: 10px;
}

.container__col-offset-4 {
  margin-left: 33.3333333333%;
}

.container__col-5 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 41.6666666667%;
  flex: 0 0 41.6666666667%;
  max-width: 41.6666666667%;
  margin-bottom: 10px;
}

.container__col-offset-5 {
  margin-left: 41.6666666667%;
}

.container__col-6 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 50%;
  flex: 0 0 50%;
  max-width: 50%;
  margin-bottom: 10px;
}

.container__col-offset-6 {
  margin-left: 50%;
}

.container__col-7 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 58.3333333333%;
  flex: 0 0 58.3333333333%;
  max-width: 58.3333333333%;
  margin-bottom: 10px;
}

.container__col-offset-7 {
  margin-left: 58.3333333333%;
}

.container__col-8 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 66.6666666667%;
  flex: 0 0 66.6666666667%;
  max-width: 66.6666666667%;
  margin-bottom: 10px;
}

.container__col-offset-8 {
  margin-left: 66.6666666667%;
}

.container__col-9 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 75%;
  flex: 0 0 75%;
  max-width: 75%;
  margin-bottom: 10px;
}

.container__col-offset-9 {
  margin-left: 75%;
}

.container__col-10 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 83.3333333333%;
  flex: 0 0 83.3333333333%;
  max-width: 83.3333333333%;
  margin-bottom: 10px;
}

.container__col-offset-10 {
  margin-left: 83.3333333333%;
}

.container__col-11 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 91.6666666667%;
  flex: 0 0 91.6666666667%;
  max-width: 91.6666666667%;
  margin-bottom: 10px;
}

.container__col-offset-11 {
  margin-left: 91.6666666667%;
}

.container__col-12 {
  padding: 0 12px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-box-flex: 0;
  -ms-flex: 0 0 100%;
  flex: 0 0 100%;
  max-width: 100%;
  margin-bottom: 10px;
}

.container__col-offset-12 {
  margin-left: 100%;
}

@media screen and (min-width: 220px) {
  .container__col-xs-offset-0 {
    margin-left: 0;
  }
  .container__col-xs-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xs-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xs-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-3 {
    margin-left: 25%;
  }
  .container__col-xs-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xs-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xs-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-6 {
    margin-left: 50%;
  }
  .container__col-xs-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xs-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xs-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-9 {
    margin-left: 75%;
  }
  .container__col-xs-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xs-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xs-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xs-offset-12 {
    margin-left: 100%;
  }
}

@media screen and (min-width: 640px) {
  .container__col-sm-offset-0 {
    margin-left: 0;
  }
  .container__col-sm-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-sm-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-sm-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-3 {
    margin-left: 25%;
  }
  .container__col-sm-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-sm-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-sm-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-6 {
    margin-left: 50%;
  }
  .container__col-sm-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-sm-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-sm-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-9 {
    margin-left: 75%;
  }
  .container__col-sm-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-sm-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-sm-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-sm-offset-12 {
    margin-left: 100%;
  }
}

@media screen and (min-width: 768px) {
  .container__col-md-offset-0 {
    margin-left: 0;
  }
  .container__col-md-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-md-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-md-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-3 {
    margin-left: 25%;
  }
  .container__col-md-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-md-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-md-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-6 {
    margin-left: 50%;
  }
  .container__col-md-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-md-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-md-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-9 {
    margin-left: 75%;
  }
  .container__col-md-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-md-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-md-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-md-offset-12 {
    margin-left: 100%;
  }
}

@media screen and (min-width: 992px) {
  .container__col-lg-offset-0 {
    margin-left: 0;
  }
  .container__col-lg-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-lg-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-lg-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-3 {
    margin-left: 25%;
  }
  .container__col-lg-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-lg-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-lg-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-6 {
    margin-left: 50%;
  }
  .container__col-lg-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-lg-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-lg-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-9 {
    margin-left: 75%;
  }
  .container__col-lg-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-lg-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-lg-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-lg-offset-12 {
    margin-left: 100%;
  }
}

@media screen and (min-width: 1200px) {
  .container__col-xl-offset-0 {
    margin-left: 0;
  }
  .container__col-xl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xl-offset-12 {
    margin-left: 100%;
  }
}

@media screen and (min-width: 1600px) {
  .container__col-xxl-offset-0 {
    margin-left: 0;
  }
  .container__col-xxl-1 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 8.3333333333%;
    flex: 0 0 8.3333333333%;
    max-width: 8.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-1 {
    margin-left: 8.3333333333%;
  }
  .container__col-xxl-2 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 16.6666666667%;
    flex: 0 0 16.6666666667%;
    max-width: 16.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-2 {
    margin-left: 16.6666666667%;
  }
  .container__col-xxl-3 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 25%;
    flex: 0 0 25%;
    max-width: 25%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-3 {
    margin-left: 25%;
  }
  .container__col-xxl-4 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 33.3333333333%;
    flex: 0 0 33.3333333333%;
    max-width: 33.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-4 {
    margin-left: 33.3333333333%;
  }
  .container__col-xxl-5 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 41.6666666667%;
    flex: 0 0 41.6666666667%;
    max-width: 41.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-5 {
    margin-left: 41.6666666667%;
  }
  .container__col-xxl-6 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 50%;
    flex: 0 0 50%;
    max-width: 50%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-6 {
    margin-left: 50%;
  }
  .container__col-xxl-7 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 58.3333333333%;
    flex: 0 0 58.3333333333%;
    max-width: 58.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-7 {
    margin-left: 58.3333333333%;
  }
  .container__col-xxl-8 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 66.6666666667%;
    flex: 0 0 66.6666666667%;
    max-width: 66.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-8 {
    margin-left: 66.6666666667%;
  }
  .container__col-xxl-9 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 75%;
    flex: 0 0 75%;
    max-width: 75%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-9 {
    margin-left: 75%;
  }
  .container__col-xxl-10 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 83.3333333333%;
    flex: 0 0 83.3333333333%;
    max-width: 83.3333333333%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-10 {
    margin-left: 83.3333333333%;
  }
  .container__col-xxl-11 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 91.6666666667%;
    flex: 0 0 91.6666666667%;
    max-width: 91.6666666667%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-11 {
    margin-left: 91.6666666667%;
  }
  .container__col-xxl-12 {
    padding: 0 12px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 0;
    -ms-flex: 0 0 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 10px;
  }
  .container__col-xxl-offset-12 {
    margin-left: 100%;
  }
}

.display-flex {
  display: flex;
}`,qi=`.filters-wrapper {
  margin: 0 auto;
  max-width: 1280px;
}

.switch-wrapper {
  display: flex;
  padding: 0px 30px;
}
.switch-wrapper .button-wrapper {
  margin-left: 50px;
}`;const Tf=Z({labelFontSize:{fontSize:"13px"}}),If=({fullText:n,switches:e,onChange:o})=>{const i=Tf();return c.createElement("div",null,c.createElement("div",{className:qi["filters-wrapper"]},c.createElement(ji,null,c.createElement("div",{className:S(Se.container__row)},n?c.createElement("div",{className:S(Se["container__col-md-3"],Se["container__col-xs-12"])},c.createElement(S3,{filterKey:n.filterKey,icon:n.icon,label:n.label,value:n.value,onChange:o})):null,c.createElement("div",{className:S(Se.container__row)},e?e.map((a,r)=>c.createElement("div",{className:qi["switch-wrapper"],key:`switchSubColumn${r}`},a.map(({switchTitle:s,switchStatus:l,button:p,label:d,buttonType:m,color:b,onClick:f,value:x,filterKey:y},g)=>p?c.createElement("div",{className:S(Se["container__col-sm-6"],Se["container__col-xs-4"],Se["center-vertical"],Se["mt-1"],qi["button-wrapper"]),key:`switch${r}${g}`},c.createElement(ze,{buttonType:m,color:b,key:`switchButton${r}${g}`,label:d,onClick:f})):c.createElement(tm,{classes:{label:i.labelFontSize},control:c.createElement(om,{checked:x,color:"primary",size:"small",onChange:u=>o(u.target.checked,y)}),label:c.createElement(c.Fragment,null,s&&c.createElement("div",null,c.createElement("b",null,s)),l&&c.createElement("div",null,l)),labelPlacement:"top"})))):null)))))};class Ec extends c.Component{constructor(){super(...arguments);h(this,"parseDescription",e=>e.replace(/^centreon\s+(\w+)/i,(o,i)=>i));h(this,"getPropsFromLicense",e=>{let o={};if(e&&e.required)if(!e.expiration_date)o={itemFooterColor:"red",itemFooterLabel:"License required"};else if(isNaN(Date.parse(e.expiration_date)))o={itemFooterColor:"red",itemFooterLabel:"License not valid"};else{const i=new Date(e.expiration_date);o={itemFooterColor:"green",itemFooterLabel:`License expires ${i.toISOString().slice(0,10)}`}}return o})}render(){const{title:e,entities:o,onCardClicked:i,onDelete:a,titleColor:r,hrColor:s,hrTitleColor:l,onInstall:p,onUpdate:d,updating:m,installing:b,type:f}=this.props;return c.createElement(ji,null,c.createElement(Jx,{hrColor:s,hrTitle:e,hrTitleColor:l}),c.createElement(Wx,null,c.createElement("div",null,o.map(x=>c.createElement("div",{className:Pe["card-inline"],id:`${f}-${x.id}`,key:x.id,onClick:()=>{i(x.id,f)}},c.createElement(Kx,{itemBorderColor:x.version.installed?x.version.outdated?"orange":"green":"gray",...this.getPropsFromLicense(x.license)},x.version.installed?c.createElement(Xx,{iconColor:"green",iconName:"state",iconPosition:"info-icon-position"}):null,c.createElement(Vi,{label:this.parseDescription(x.description),labelStyle:{fontSize:"16px"},title:x.description,titleColor:r},c.createElement(H3,{label:`by ${x.label}`})),c.createElement(ze,{buttonType:x.version.installed?x.version.outdated?"regular":"bordered":"regular",color:x.version.installed?x.version.outdated?"orange":"blue":"green",customClass:"button-card-position",label:(x.version.installed?"":"Available ")+x.version.available,style:{cursor:x.version.installed?"default":"pointer",opacity:b[x.id]||m[x.id]?"0.5":"inherit"},onClick:y=>{y.preventDefault(),y.stopPropagation();const{id:g}=x,{version:u}=x;u.outdated&&!m[x.id]?d(g,f):!u.installed&&!b[x.id]&&p(g,f)}},x.version.installed?x.version.outdated?c.createElement(Vt,{customClass:"content-icon-button",iconContentColor:"white",iconContentType:"update",loading:m[x.id]}):null:c.createElement(Vt,{customClass:"content-icon-button",iconContentColor:"white",iconContentType:`${b[x.id]?"update":"add"}`,loading:b[x.id]})),x.version.installed?c.createElement(Vx,{buttonActionType:"delete",buttonIconType:"delete",customPosition:"button-action-card-position",iconColor:"gray",onClick:y=>{y.preventDefault(),y.stopPropagation(),a(x,f)}}):null))))))}}const Ac=3.8,Wi=Z(n=>({nextContent:{marginTop:n.spacing(1.5)}})),Ce=({animate:n,...e})=>c.createElement(fo,{animation:n?"wave":!1,...e}),Nf=({animate:n=!0})=>{const e=de();return c.createElement(Ce,{animate:n,height:e.spacing(50),variant:"rect",width:"100%"})},Rf=({animate:n=!0})=>{const e=de(),o=Wi();return c.createElement(c.Fragment,null,c.createElement(Ce,{animate:n,height:e.spacing(Ac),variant:"rect",width:e.spacing(10)}),c.createElement(Ce,{animate:n,className:o.nextContent,height:e.spacing(Ac),variant:"rect",width:e.spacing(20)}))},Gf=({animate:n=!0})=>{const e=de(),o=Wi();return c.createElement(c.Fragment,null,c.createElement(Ce,{animate:n,variant:"text",width:e.spacing(20)}),c.createElement(Ce,{animate:n,className:o.nextContent,variant:"text",width:e.spacing(15)}),c.createElement(Ce,{animate:n,className:o.nextContent,variant:"text",width:e.spacing(25)}))},Df=({animate:n=!0})=>{const e=de(),o=Wi();return c.createElement(c.Fragment,null,c.createElement(Ce,{animate:n,variant:"text",width:e.spacing(15)}),c.createElement(Ce,{animate:n,className:o.nextContent,variant:"text",width:e.spacing(25)}))};class Hf extends c.Component{render(){const{type:e,modalDetails:o,onCloseClicked:i,onDeleteClicked:a,onUpdateClicked:r,onInstallClicked:s,loading:l,animate:p}=this.props;return o===null?null:c.createElement(Qr,{popupType:"big"},l?c.createElement(Nf,{animate:p}):c.createElement(G3,{images:!l&&o.images?o.images:[],type:e},o.version.installed&&o.version.outdated?c.createElement(Vt,{customClass:"content-icon-popup-wrapper",iconContentColor:"orange",iconContentType:"update",onClick:()=>{r(o.id,o.type)}}):null,o.version.installed?c.createElement(Vt,{customClass:"content-icon-popup-wrapper",iconContentColor:"red",iconContentType:"delete",onClick:()=>{a(o.id,o.type)}}):c.createElement(Vt,{customClass:"content-icon-popup-wrapper",iconContentColor:"green",iconContentType:"add",onClick:()=>{s(o.id,o.type)}})),c.createElement("div",{className:S(On["popup-header"])},l?c.createElement(Rf,{animate:p}):c.createElement(c.Fragment,null,c.createElement(Vi,{label:o.title}),c.createElement(ze,{buttonType:"regular",color:"blue",label:(o.version.installed?"":"Available ")+o.version.available,style:{cursor:"default"}}),c.createElement(ze,{buttonType:"bordered",color:"gray",label:o.stability,style:{cursor:"default",margin:"15px"}}))),c.createElement(Xr,null),c.createElement("div",{className:S(On["popup-body"])},l?c.createElement(Gf,{animate:p}):c.createElement(c.Fragment,null,o.last_update?c.createElement(So,{date:`Last update ${o.last_update}`}):null,c.createElement(So,{title:"Description:"}),c.createElement(So,{text:o.description}))),c.createElement(Xr,null),c.createElement("div",{className:S(On["popup-footer"])},l?c.createElement(Df,{animate:p}):c.createElement(So,{link:!0,note:o.release_note})),c.createElement(Jr,{iconPosition:"icon-close-position-big",iconType:"big",onClick:i}))}}class Ff extends c.Component{render(){const{deletingEntity:e,onConfirm:o,onCancel:i}=this.props;return c.createElement(Qr,{popupType:"small"},c.createElement("div",{className:S(On["popup-header"])},c.createElement(Vi,{label:e.description})),c.createElement("div",{className:S(On["popup-body"])},c.createElement(A3,{messageInfo:"red",text:"Do you want to delete this extension? This action will remove all associated data."})),c.createElement("div",{className:S(On["popup-footer"])},c.createElement("div",{className:S(On.container__row)},c.createElement("div",{className:S(On["container__col-xs-6"])},c.createElement(ze,{buttonType:"regular",color:"red",label:"Delete",onClick:a=>{a.preventDefault(),a.stopPropagation(),o(e.id,e.type)}})),c.createElement("div",{className:S(On["container__col-xs-6"],["text-left"])},c.createElement(ze,{buttonType:"regular",color:"gray",label:"Cancel",onClick:a=>{a.preventDefault(),a.stopPropagation(),i()}})))),c.createElement(Jr,{iconPosition:"icon-close-position-middle",iconType:"middle",onClick:a=>{a.preventDefault(),a.stopPropagation(),i()}}))}}const Pf=im({palette:{action:{acknowledged:"#AA9C24",acknowledgedBackground:"#F7F4E5",inDowntime:"#9C27B0",inDowntimeBackground:"#F9E7FF"},background:{default:"#EDEDED"},error:{main:"#f90026"},info:{main:"#00d3d4"},primary:{main:"#10069F"},success:{main:"#84BD00"},warning:{main:"#FF9A13"}},typography:{body1:{fontSize:"0.875rem"},body2:{fontSize:"0.75rem"},caption:{fontSize:"0.625rem"}}}),Lf=({children:n,...e})=>c.createElement(am,{theme:Pf,...e},n),$e=({open:n,onClose:e,onCancel:o,onConfirm:i,labelTitle:a,labelCancel:r="Cancel",labelConfirm:s="Confirm",children:l,contentWidth:p,confirmDisabled:d=!1,submitting:m=!1,...b})=>c.createElement(rm,{open:n,onClose:e,...b},a&&c.createElement(cm,null,a),l&&c.createElement(sm,{style:{overflowY:"visible",width:p}},l),c.createElement(lm,null,o&&c.createElement(si,{color:"primary",onClick:o},r),c.createElement(si,{color:"primary",disabled:d,endIcon:m&&c.createElement(mo,{size:15}),onClick:i},s))),Ki=({labelMessage:n,...e})=>c.createElement($e,{...e},n&&c.createElement(pm,null,n));Ki.propTypes={labelCancel:ee.string,labelConfirm:ee.string,labelMessage:ee.string,labelTitle:ee.string,onCancel:ee.func.isRequired,onClose:ee.func,onConfirm:ee.func.isRequired,open:ee.bool.isRequired},Ki.defaultProps={labelCancel:"Cancel",labelConfirm:"Confirm",labelMessage:null,labelTitle:"are you sure ?",onClose:null};const vc=Z(n=>({skeletonLayout:{borderRadius:n.spacing(.5)}})),Me=({animate:n,...e})=>{const o=vc();return c.createElement(fo,{animation:n?"wave":!1,className:o.skeletonLayout,variant:"rect",width:"100%",...e})},zc=2,$f=7.4,Mf=4,Of=3.75,Bf=40,Uf=Z(n=>({actionBarPaginationContainer:{alignItems:"center",display:"grid",gridTemplateColumns:`${n.spacing(50)}px ${n.spacing(54)}px`,justifyContent:"space-between",marginLeft:n.spacing(3),marginTop:n.spacing(1.25)},actionBarSkeleton:{columnGap:`${n.spacing(1)}px`,display:"grid",gridTemplateColumns:`repeat(${zc}, ${n.spacing(10)}px)`},contentSkeleton:{marginLeft:n.spacing(2),marginTop:n.spacing(1)}})),Vf=({animate:n})=>{const e=de(),o=Uf();return c.createElement(c.Fragment,null,c.createElement(Me,{animate:n,height:e.spacing($f)}),c.createElement("div",{className:o.actionBarPaginationContainer},c.createElement("div",{className:o.actionBarSkeleton},Array(zc).fill(null).map((i,a)=>c.createElement(Me,{animate:n,height:e.spacing(Of),key:a.toString()}))),c.createElement(Me,{animate:n,height:e.spacing(Mf)})),c.createElement("div",{className:o.contentSkeleton},c.createElement(Me,{animate:n,height:e.spacing(Bf)})))},jf=6.5,Sc=3.8,qf=Z(n=>({breadcrumbSkeleton:{margin:n.spacing(.5,2),width:n.spacing(30)},headerContentFooterContainer:{alignContent:"space-between",display:"grid",gridTemplateRows:`auto ${n.spacing(Sc)}`,height:"100%",rowGap:`${n.spacing(1)}px`},menuContentContainer:{display:"grid",gridTemplateColumns:`${n.spacing(5.5)}px 1fr`,height:"100%"},skeletonContainer:{height:"100%",width:"100%"}})),Kt=({displayHeaderAndNavigation:n=!1,animate:e=!0})=>{const o=qf(),i=vc(),a=de();return c.createElement("div",{className:o.skeletonContainer},c.createElement("div",{className:S({[o.menuContentContainer]:n})},c.createElement(Me,{animate:e,height:"100%",width:`calc(100% - ${a.spacing(.5)}px)`}),c.createElement("div",{className:o.headerContentFooterContainer},c.createElement("div",null,n&&c.createElement(Me,{animate:e,height:a.spacing(jf)}),c.createElement(fo,{animation:e?"wave":!1,className:S(o.breadcrumbSkeleton,i.skeletonLayout),height:a.spacing(2.5),variant:"text"}),c.createElement(Vf,{animate:e})),n&&c.createElement(Me,{animate:e,height:a.spacing(Sc)}))))};var Go=`@charset "UTF-8";
/* Colors */
/* Fonts */
.center-both {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  justify-content: center;
}
.center-vertical {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
}
.center-horizontal {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
}
.center-baseline {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: baseline;
}
.center-none {
  display: -webkit-box;
  display: -moz-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
}
.flex-none {
  flex: none !important;
  max-width: none !important;
}
.opacity-1-1 {
  opacity: 1;
}
.opacity-1-2 {
  opacity: 0.5;
}
.opacity-1-3 {
  opacity: 0.3333333333;
}
.opacity-1-4 {
  opacity: 0.25;
}
.opacity-1-5 {
  opacity: 0.2;
}
.opacity-1-6 {
  opacity: 0.1666666667;
}
.opacity-1-7 {
  opacity: 0.1428571429;
}
.opacity-1-8 {
  opacity: 0.125;
}
.opacity-1-9 {
  opacity: 0.1111111111;
}
.opacity-1-10 {
  opacity: 0.1;
}
.opacity-2-1 {
  opacity: 2;
}
.opacity-2-2 {
  opacity: 1;
}
.opacity-2-3 {
  opacity: 0.6666666667;
}
.opacity-2-4 {
  opacity: 0.5;
}
.opacity-2-5 {
  opacity: 0.4;
}
.opacity-2-6 {
  opacity: 0.3333333333;
}
.opacity-2-7 {
  opacity: 0.2857142857;
}
.opacity-2-8 {
  opacity: 0.25;
}
.opacity-2-9 {
  opacity: 0.2222222222;
}
.opacity-2-10 {
  opacity: 0.2;
}
.opacity-3-1 {
  opacity: 3;
}
.opacity-3-2 {
  opacity: 1.5;
}
.opacity-3-3 {
  opacity: 1;
}
.opacity-3-4 {
  opacity: 0.75;
}
.opacity-3-5 {
  opacity: 0.6;
}
.opacity-3-6 {
  opacity: 0.5;
}
.opacity-3-7 {
  opacity: 0.4285714286;
}
.opacity-3-8 {
  opacity: 0.375;
}
.opacity-3-9 {
  opacity: 0.3333333333;
}
.opacity-3-10 {
  opacity: 0.3;
}
.opacity-4-1 {
  opacity: 4;
}
.opacity-4-2 {
  opacity: 2;
}
.opacity-4-3 {
  opacity: 1.3333333333;
}
.opacity-4-4 {
  opacity: 1;
}
.opacity-4-5 {
  opacity: 0.8;
}
.opacity-4-6 {
  opacity: 0.6666666667;
}
.opacity-4-7 {
  opacity: 0.5714285714;
}
.opacity-4-8 {
  opacity: 0.5;
}
.opacity-4-9 {
  opacity: 0.4444444444;
}
.opacity-4-10 {
  opacity: 0.4;
}
.opacity-5-1 {
  opacity: 5;
}
.opacity-5-2 {
  opacity: 2.5;
}
.opacity-5-3 {
  opacity: 1.6666666667;
}
.opacity-5-4 {
  opacity: 1.25;
}
.opacity-5-5 {
  opacity: 1;
}
.opacity-5-6 {
  opacity: 0.8333333333;
}
.opacity-5-7 {
  opacity: 0.7142857143;
}
.opacity-5-8 {
  opacity: 0.625;
}
.opacity-5-9 {
  opacity: 0.5555555556;
}
.opacity-5-10 {
  opacity: 0.5;
}
.opacity-6-1 {
  opacity: 6;
}
.opacity-6-2 {
  opacity: 3;
}
.opacity-6-3 {
  opacity: 2;
}
.opacity-6-4 {
  opacity: 1.5;
}
.opacity-6-5 {
  opacity: 1.2;
}
.opacity-6-6 {
  opacity: 1;
}
.opacity-6-7 {
  opacity: 0.8571428571;
}
.opacity-6-8 {
  opacity: 0.75;
}
.opacity-6-9 {
  opacity: 0.6666666667;
}
.opacity-6-10 {
  opacity: 0.6;
}
.opacity-7-1 {
  opacity: 7;
}
.opacity-7-2 {
  opacity: 3.5;
}
.opacity-7-3 {
  opacity: 2.3333333333;
}
.opacity-7-4 {
  opacity: 1.75;
}
.opacity-7-5 {
  opacity: 1.4;
}
.opacity-7-6 {
  opacity: 1.1666666667;
}
.opacity-7-7 {
  opacity: 1;
}
.opacity-7-8 {
  opacity: 0.875;
}
.opacity-7-9 {
  opacity: 0.7777777778;
}
.opacity-7-10 {
  opacity: 0.7;
}
.opacity-8-1 {
  opacity: 8;
}
.opacity-8-2 {
  opacity: 4;
}
.opacity-8-3 {
  opacity: 2.6666666667;
}
.opacity-8-4 {
  opacity: 2;
}
.opacity-8-5 {
  opacity: 1.6;
}
.opacity-8-6 {
  opacity: 1.3333333333;
}
.opacity-8-7 {
  opacity: 1.1428571429;
}
.opacity-8-8 {
  opacity: 1;
}
.opacity-8-9 {
  opacity: 0.8888888889;
}
.opacity-8-10 {
  opacity: 0.8;
}
.opacity-9-1 {
  opacity: 9;
}
.opacity-9-2 {
  opacity: 4.5;
}
.opacity-9-3 {
  opacity: 3;
}
.opacity-9-4 {
  opacity: 2.25;
}
.opacity-9-5 {
  opacity: 1.8;
}
.opacity-9-6 {
  opacity: 1.5;
}
.opacity-9-7 {
  opacity: 1.2857142857;
}
.opacity-9-8 {
  opacity: 1.125;
}
.opacity-9-9 {
  opacity: 1;
}
.opacity-9-10 {
  opacity: 0.9;
}
.opacity-10-1 {
  opacity: 10;
}
.opacity-10-2 {
  opacity: 5;
}
.opacity-10-3 {
  opacity: 3.3333333333;
}
.opacity-10-4 {
  opacity: 2.5;
}
.opacity-10-5 {
  opacity: 2;
}
.opacity-10-6 {
  opacity: 1.6666666667;
}
.opacity-10-7 {
  opacity: 1.4285714286;
}
.opacity-10-8 {
  opacity: 1.25;
}
.opacity-10-9 {
  opacity: 1.1111111111;
}
.opacity-10-10 {
  opacity: 1;
}
@font-face {
  font-family: "icomoon";
  src: url("__VITE_ASSET__86322399__");
  src: url("__VITE_ASSET__86322399__") format("embedded-opentype"), url("__VITE_ASSET__434e2b1b__") format("truetype"), url("__VITE_ASSET__8b06532b__") format("woff"), url("__VITE_ASSET__9d46154f__") format("svg");
  font-weight: normal;
  font-style: normal;
}
[class^=icon-], [class*=" icon-"] {
  /* use !important to prevent issues with browser extensions that change fonts */
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  /* Better Font Rendering =========== */
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.icon-home:before {
  content: "\uE904";
  color: #009f98;
}
.icon-home:hover:before {
  content: "\uE904";
  color: #ffffff;
}
.icon-monitoring:before {
  content: "\uE907";
  color: #88bd23;
}
.icon-monitoring:hover:before {
  content: "\uE907";
  color: #ffffff;
}
.icon-reporting:before {
  content: "\uE909";
  color: #ffa200;
}
.icon-reporting:hover:before {
  content: "\uE909";
  color: #ffffff;
}
.icon-configuration:before {
  content: "\uE902";
  color: #009fe9;
}
.icon-configuration:hover:before {
  content: "\uE902";
  color: #ffffff;
}
.icon-administration:before {
  content: "\uE900";
  color: #2b2d84;
}
.icon-administration:hover:before {
  content: "\uE900";
  color: #ffffff;
}
.icon-poller:before {
  content: "\uE908";
  color: #ffffff;
}
.icon-link:before {
  content: "\uE906";
  color: #232f39;
}
.icon-clock:before {
  content: "\uE901";
  color: #232f39;
}
.icon-database:before {
  content: "\uE903";
  color: #232f39;
}
.icon-hosts:before {
  content: "\uE905";
  color: #ffffff;
}
.icon-services:before {
  content: "\uE90A";
  color: #ffffff;
}
.icon-top-counter:before {
  content: "\uE90E";
  color: #ffffff;
}
.icon-user:before {
  content: "\uE90B";
  color: #ffffff;
}
.iconmoon {
  display: block;
  text-align: center;
  height: 26px;
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.iconmoon:before {
  font-size: 24px;
}
.icons-wrap {
  margin-right: 6px;
  cursor: pointer;
  position: relative;
  height: 39px;
}
.icons__name {
  display: block;
  font-size: 0.6875rem;
  color: #ffffff;
  font-family: "Roboto Light" !important;
  text-transform: lowercase;
}`;const Cc=({iconType:n,iconName:e,style:o,onClick:i,children:a})=>c.createElement("span",{className:S(Go["icons-wrap"]),style:o},c.createElement("span",{className:S(Go.iconmoon,{[Go[`icon-${n}`]]:!0}),onClick:i}),c.createElement("span",{className:S(Go.icons__name)},e),a);var st=`/* Colors */
/* Fonts */
.icons-number {
  font-size: 0.875rem;
  padding: 0px 5px;
  position: relative;
  color: #ffffff;
  font-family: "Roboto Bold";
  overflow: hidden;
  text-align: center;
  min-width: 32px;
  height: 32px;
  border-radius: 50px;
  margin: 0 6px;
  box-sizing: border-box;
  text-decoration: none;
  cursor: pointer;
  display: inline-block;
}
.icons-number.bordered.red {
  border: 2px solid #ed1c24;
}
.icons-number.bordered.gray-dark {
  border: 2px solid #818185;
}
.icons-number.bordered.gray-light {
  border: 2px solid #cdcdcd;
}
.icons-number.bordered.green {
  border: 2px solid #87bd23;
}
.icons-number.bordered.orange {
  border: 2px solid #ff9913;
}
.icons-number.bordered.blue {
  border: 2px solid #2ad1d4;
}
.icons-number.colored.red {
  background-color: #ed1c24;
}
.icons-number.colored.gray-dark {
  background-color: #818185;
}
.icons-number.colored.gray-light {
  background-color: #cdcdcd;
}
.icons-number.colored.green {
  background-color: #87bd23;
}
.icons-number.colored.orange {
  background-color: #ff9913;
}
.icons-number.colored.blue {
  background-color: #2ad1d4;
}
.icons-number.colored .number-count {
  line-height: 32px;
}
.icons .number-wrap {
  font-size: 0.875rem;
  font-family: "Roboto Bold";
  position: relative;
  text-decoration: none;
}
.icons .number-count {
  line-height: 32px;
  color: #ffffff;
  font-family: "Roboto Bold" !important;
}`;const Oe=({iconColor:n,iconType:e,iconNumber:o})=>c.createElement("span",{className:S(st.icons,st["icons-number"],st[e],st[n],st["number-wrap"])},c.createElement("span",{className:S(st["number-count"])},o));var ke=`@charset "UTF-8";
/* Colors */
/* Fonts */
.submenu-top, .submenu-bottom {
  display: flex;
}
.submenu-top {
  position: relative;
  padding: 6px 20px 7px 20px;
  flex-wrap: wrap;
  align-items: center;
  background-color: #232f39;
}
.submenu-top .icons-toggle-arrow {
  position: absolute;
}
.submenu-toggle {
  display: none;
}
.submenu-items {
  list-style: none;
  padding: 0;
  margin: 0;
}
.submenu-item {
  position: relative;
  width: 100%;
  float: left;
  display: block;
}
.submenu-item-count {
  float: right;
  padding: 10px;
  font-family: "Roboto Light";
  color: #ffffff;
  font-size: 0.8rem;
  text-decoration: none;
  position: absolute;
  right: 0;
}
.submenu-item-title {
  position: relative;
  float: left;
  padding: 10px;
  font-family: "Roboto Light";
  color: #ffffff;
  font-size: 0.8rem;
  text-decoration: none;
  display: block;
  padding-left: 10px;
}
.submenu-item-dotted {
  padding-left: 22px;
}
.submenu-item-dot {
  position: absolute;
  left: 10px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  top: 50%;
  transform: translateY(-50%);
}
.submenu-item-dot.dot-red {
  background-color: #ed1b23;
}
.submenu-item-dot.dot-orange {
  background-color: #ffa125;
}
.submenu-item-dot.dot-gray {
  background-color: #818185;
}
.submenu-item-dot.dot-gray-light {
  background-color: #cdcdcd;
}
.submenu-item-dot.dot-green {
  background-color: #87bd23;
}
.submenu-item-dot.dot-blue {
  background-color: #2ad1d4;
}
.submenu-toggle {
  position: absolute;
  left: 0px;
  background-color: #232f39;
  padding: 10px;
  top: 100%;
  z-index: 99;
  box-sizing: border-box;
  text-align: left;
  width: 100%;
}
.submenu-active {
  background-color: #000915;
}
.submenu-active .icons-toggle-arrow {
  transform: rotateZ(180deg);
}
.submenu-active .submenu-toggle {
  display: block;
}
.submenu-header .profile-user-type, .submenu-header .profile-user-edit, .submenu-header .profile-user-name {
  font-family: "Roboto Light";
  color: #ffffff;
  text-decoration: none;
  font-size: 0.8rem;
}
.submenu-header .profile-user-type {
  display: inline-block;
  width: 51%;
  overflow: hidden;
  text-overflow: ellipsis;
}
.submenu-header .profile-user-edit {
  margin-left: 4px;
  float: right;
  text-decoration: underline;
}
.submenu-header .profile-user-name {
  display: block;
  margin-bottom: 10px;
}
.submenu-header .profile-user-button {
  font-size: 0.785rem;
  color: #ffa225;
  text-decoration: none;
  font-family: "Roboto Light";
  text-align: left;
  border-radius: 16px;
  border: 1px solid #ffa125;
  background-color: transparent;
  display: block;
  width: 100%;
  outline: none;
  position: relative;
  padding: 5px 27px 5px 10px;
  box-sizing: border-box;
}
.submenu-header .profile-user-button:hover {
  color: #000916;
  background-color: #ffa125;
}
.submenu-header .profile-user-button:hover .btn-logout-icon:before, .submenu-header .profile-user-button:hover .icon-copied:before {
  color: #000915;
}
.submenu-header .profile-user-button span:first-child {
  line-height: 17px;
  display: block;
}
.submenu-header .profile-bookmark-link {
  margin: 14px 9px 13px;
  width: 62%;
  border: 1px solid #bcbdc0;
  padding: 7px 6px;
  border-radius: 3px;
  outline: none;
  box-sizing: border-box;
}
.submenu-header .btn-logout-icon {
  width: 19px;
  height: 19px;
  position: absolute;
  right: 9px;
  top: 50%;
  transform: translateY(-50%);
}
.submenu-header .btn-logout-icon:before {
  content: "\uE90D";
  color: #ffa125;
  font-size: 19px;
}
.submenu-header .btn-logout-icon.icon-copied:before {
  content: "\uE90C";
  color: #000915;
  color: #ffa125;
}
.submenu-header .button-wrap .btn {
  margin-top: 10px;
  float: right;
}`;class Tc extends c.Component{render(){const{submenuType:e,children:o,active:i,...a}=this.props;return c.createElement("div",{className:S(ke[`submenu-${e}`],{[ke["submenu-active"]]:!!i}),...a},o)}}class Ic extends c.Component{render(){return this.props,c.createElement("ul",{className:S(ke["submenu-items"])})}}class se extends c.Component{render(){const{dotColored:e,submenuTitle:o,submenuCount:i}=this.props;return c.createElement("li",{className:S(ke["submenu-item"])},c.createElement("span",{className:S(ke["submenu-item-title"],{[ke["submenu-item-dotted"]]:!!e})},c.createElement("span",{className:S(ke["submenu-item-dot"],ke[`dot-${e}`])}),o),c.createElement("span",{className:S(ke["submenu-item-count"])},i))}}const Wf=Z(()=>({alignCenter:{alignItems:"center",display:"grid",height:"100%",justifyContent:"center",width:"100%"}})),Yt=({loading:n,children:e,loadingIndicatorSize:o=void 0,alignCenter:i=!0})=>{const a=Wf();return n?c.createElement("div",{className:S({[a.alignCenter]:i})},c.createElement(mo,{size:o})):e},Kf=Z(n=>({details:{padding:n.spacing(0,2)}})),Yf=hr(St)(({theme:n})=>({fontSize:n.typography.pxToRem(15),fontWeight:700})),Qf=hr(gr)({backgroundColor:"transparent",borderBottom:"1px solid #bcbdc0",borderRadius:"0",boxShadow:"none",margin:"0",width:"100%"}),Jf=It(n=>({content:{"&$expanded":{margin:n.spacing(1,0)}},expanded:{},root:{"&$expanded":{minHeight:n.spacing(4)},minHeight:n.spacing(4)}}))(xr),Xf=({title:n,children:e})=>{const o=Kf();return c.createElement(Qf,null,c.createElement(Jf,{expandIcon:c.createElement(yr,null)},c.createElement(Yf,null,n)),c.createElement(fr,{className:o.details},c.createElement(wr,null,e)))},Yi=550,Nc=20,Zf=Z(n=>({closeIcon:{margin:"auto",width:15},closeSecondaryPanelBar:{alignContent:"center",alignItems:"center",backgroundColor:n.palette.background.default,cursor:"pointer",display:"flex"},container:{display:e=>e?"grid":"block",gridTemplateColumns:e=>e?`1fr ${Nc}px 1fr`:"100%",height:"100%"},mainPanel:{bottom:0,left:0,overflow:"auto",position:e=>e?"unset":"absolute",right:0,top:0,width:Yi}})),ng=({header:n,secondaryPanel:e,sections:o,onSecondaryPanelClose:i=()=>{},onClose:a=()=>{},loading:r=!1})=>{const s=!Fn(e),l=Zf(s),p=()=>s?Yi*2+Nc:Yi;return c.createElement(kc,{header:n,selectedTab:c.createElement(Yt,{alignCenter:!0,loading:r},c.createElement("div",{className:l.container},c.createElement(dm,{className:l.mainPanel},o.map(({id:d,title:m,section:b,expandable:f})=>f?c.createElement(Xf,{key:d,title:m},b):c.createElement(wr,{key:d},b))),s&&c.createElement(xo,{"aria-label":"Close Secondary Panel",className:l.closeSecondaryPanelBar,onClick:i},c.createElement(mm,{className:l.closeIcon,color:"action"})),c.createElement(ur,{direction:"left",in:s,timeout:{enter:150,exit:50}},c.createElement("div",null,e)))),width:p(),onClose:a})},eg=({memoProps:n=[],sections:e,secondaryPanel:o,loading:i,...a})=>re({Component:c.createElement(ng,{loading:i,secondaryPanel:o,sections:e,...a}),memoProps:[...n,e,o,i]});var Be;(function(n){n[n.High=1]="High",n[n.Medium=2]="Medium",n[n.Low=3]="Low",n[n.Pending=4]="Pending",n[n.Ok=5]="Ok",n[n.None=6]="None"})(Be||(Be={}));const Qi=({theme:n,severityCode:e})=>{const{palette:o}=n;return{[1]:{backgroundColor:o.error.main,color:o.common.white},[2]:{backgroundColor:o.warning.main,color:o.common.black},[3]:{backgroundColor:o.action.disabled,color:o.common.black},[4]:{backgroundColor:o.info.main,color:o.common.white},[5]:{backgroundColor:o.success.main,color:o.common.black},[6]:{backgroundColor:dr(o.primary.main,.1),color:o.primary.main}}[e]},tg=Z(n=>({chip:({severityCode:e,label:o})=>({...Qi({severityCode:e,theme:n}),...!o&&{borderRadius:n.spacing(1.5),height:n.spacing(1.5),width:n.spacing(1.5)},"&:hover":{...Qi({severityCode:e,theme:n})}})})),Ue=({severityCode:n,label:e,clickable:o=!1,...i})=>{const a=tg({label:e,severityCode:n});return c.createElement(ar,{className:a.chip,clickable:o,label:e==null?void 0:e.toUpperCase(),size:"small",...i})},og=({name:n,value:e})=>`${n}=${encodeURIComponent(JSON.stringify(e))}`,ig=n=>n.filter(({value:e})=>!Fn(e)&&!bo(e)).map(og).join("&"),ag=({value:n,fields:e})=>e.map(i=>{const a=`(?:^|\\s)${i.replace(".","\\.")}:([^\\s]+)`,[,r]=(n==null?void 0:n.match(a))||[];return{field:i,value:r}}).filter(Xe("value")),rg=n=>{if(n===void 0)return;const e=ag(n);if(!bo(e))return{$and:e.map(({field:a,value:r})=>({[a]:{$rg:r}}))};const{value:o,fields:i}=n;return{$or:i.map(a=>({[a]:{$rg:o}}))}},cg=n=>{if(n!==void 0)return{$and:n.map(({field:e,values:o})=>({[e]:{$in:o}}))}},sg=n=>n===void 0?void 0:{$and:li(xm(({field:o,values:i,value:a})=>Fn(a)?gm(i||{}).map(([r,s])=>({[o]:{[r]:s}})):[{[o]:a}]),fm)(n)},lg=n=>{if(n===void 0)return;const{regex:e,lists:o,conditions:i}=n,a=rg(e),r=cg(o),s=sg(i),l=pr(Fn,[a,r,s]);return l.length===1?bm(l):{$and:l}},pg=({sort:n,page:e,limit:o,search:i,customQueryParameters:a=[]})=>[{name:"page",value:e},{name:"limit",value:o},{name:"sort_by",value:n},{name:"search",value:lg(i)},...a],dg=({baseEndpoint:n,queryParameters:e})=>`${n}?${ig(e)}`,lt=({baseEndpoint:n,parameters:e,customQueryParameters:o})=>dg({baseEndpoint:n,queryParameters:[...pg({...e,customQueryParameters:o})]}),mg=Ze.object({limit:Ze.number,page:Ze.number,total:Ze.number},"ListingMeta"),Rc=({entityDecoder:n,entityDecoderName:e,listingDecoderName:o})=>Ze.object({meta:mg,result:Ze.array(n,e)},o);var bg={shortEN:{d:()=>"d",h:()=>"h",m:()=>"m",mo:()=>"mo",ms:()=>"ms",s:()=>"s",w:()=>"w",y:()=>"y"},shortES:{d:()=>"d",h:()=>"h",m:()=>"m",mo:()=>"mes",ms:()=>"ms",s:()=>"s",w:()=>"sem",y:()=>"a"},shortFR:{d:()=>"j",h:()=>"h",m:()=>"m",mo:()=>"mo",ms:()=>"ms",s:()=>"s",w:()=>"sem",y:()=>"a"},shortPT:{d:()=>"d",h:()=>"h",m:()=>"m",mo:()=>"m\xEAs",ms:()=>"ms",s:()=>"s",w:()=>"sem",y:()=>"a"}};const Qt="L",Jt="LT",pt=`${Qt} ${Jt}`,Pn=()=>{const{locale:n,timezone:e}=ym(),o=({date:p,formatString:d})=>{const m=n.substring(0,2);return um(p).tz(e).locale(m).format(d)};return{format:o,toDate:p=>o({date:p,formatString:Qt}),toDateTime:p=>o({date:p,formatString:pt}),toHumanizedDuration:p=>{const d=hm.humanizer();d.languages=bg;const m=n.substring(0,2).toUpperCase();return d(p*1e3,{delimiter:" ",fallbacks:["shortEN"],language:`short${m}`,round:!0,serialComma:!1,spacer:""})},toIsoString:p=>`${new Date(p).toISOString().substring(0,19)}Z`,toTime:p=>o({date:p,formatString:Jt})}},Gc=n=>{const e=document.createElement("textarea");document.body.appendChild(e),e.value=n,e.select(),document.execCommand("copy"),document.body.removeChild(e)},Do=n=>{const e=new URLSearchParams(window.location.search);n.forEach(({name:o,value:i})=>{e.set(o,JSON.stringify(i))}),window.history.pushState({},"",`${window.location.pathname}?${e.toString()}`)},Xt=()=>{const e=[...new URLSearchParams(window.location.search).entries()].map(([o,i])=>[o,JSON.parse(i)]);return wm(e)},xg="@axios/GET_DATA",fg="@axios/POST_DATA",gg="@axios/PUT_DATA",yg="@axios/DELETE_DATA",Dc="@axios/SET_AXIOS_DATA",ug="@axios/UPLOAD_DATA",Ji="@axios/FILE_UPLOAD_PROGRESS",hg="@axios/RESET_UPLOAD_PROGRESS_DATA";function*wg(){yield nt(xg,Ho)}function*_g(){yield nt(fg,Ho)}function*kg(){yield nt(gg,Ho)}function*Eg(){yield nt(yg,Ho)}function*Ag(){yield nt(ug,Ig)}function*vg(){yield nt(hg,zg)}function*zg(n){try{yield mi({data:{reset:!0},type:Ji}),n.resolve()}catch(e){n.reject(e)}}const Sg=({files:n,url:e},o)=>{const i=new FormData;for(const r of n)i.append("file[]",r);const a={headers:{"Content-Type":"multipart/form-data"},onUploadProgress:o,withCredentials:!0};return he.post(e,i,a)},Cg=n=>{let e;const o=_m(r=>(e=r,()=>{}));return[Sg(n,r=>{const{total:s,loaded:l}=r,p=Math.round(l*100/s);e({[n.fileIndex]:p}),p===100&&e(Em)}),o]};function*Tg(n){for(;;){const e=yield km(n);yield mi({data:e,type:Ji})}}function*Ig(n){try{let e={result:{errors:[],successed:[]},status:!1};const o=yield _r(n.files.map((i,a)=>bi(Ng,{...n,fileIndex:a,files:[i]})));for(const i of o)i.result.errors&&(e={result:{...e.result,errors:[...e.result.errors,...i.result.errors]},status:!0}),i.result.successed&&(e={result:{...e.result,successed:[...e.result.successed,...i.result.successed]},status:!0});n.resolve(e)}catch(e){n.reject(e)}}function*Ng(n){const[e,o]=yield bi(Cg,n);yield De(Tg,o);try{return yield(yield bi(()=>e)).data}catch(i){throw i}}function*Ho(n){try{if(n.requestType){let e=null;n.requestType==="DELETE"?e=n.data?{data:n.data}:null:e=n.data?n.data:null;const i=yield(yield he[n.requestType.toLowerCase()](n.url,e||null)).data,{propKey:a}=n;a&&(yield mi({data:i,propKey:a,type:Dc})),i?n.resolve(i):n.reject("No data in response")}else throw new Error("Request type is required!")}catch(e){n.reject(e)}}const Rg=function*(){yield _r([De(wg),De(_g),De(kg),De(Eg),De(Ag),De(vg)])},Hc="@poller/SET_POLLER_WIZARD_DATA",Fo=n=>({pollerData:n,type:Hc}),Gg={},Dg=(n=Gg,e)=>{switch(e.type){case Hc:return{...n,...e.pollerData};default:return n}},wn=n=>he.create({baseURL:`./api/${n}`}),Hg="FETCH_NAVIGATION_BEGIN",Fc="FETCH_NAVIGATION_SUCCESS",Fg="FETCH_NAVIGATION_FAILURE",Pc=()=>async n=>{n(Pg());try{const{data:e}=await wn("internal.php?object=centreon_topology&action=navigationList").get();n(Lg(e.result))}catch(e){n($g(e))}},Pg=()=>({type:Hg}),Lg=n=>({items:n,type:Fc}),$g=n=>({error:n,type:Fg}),Mg={fetched:!1,items:[]},Og=(n=Mg,e)=>{switch(e.type){case Fc:return{...n,fetched:!0,items:e.items};case"@@router/LOCATION_CHANGE":const o=document.createEvent("CustomEvent");return o.initCustomEvent("react.href.update",!1,!1,{href:window.location.href}),window.dispatchEvent(o),n;default:return n}},Lc="@header/SET_REFRESH_INTERVALS",Bg=n=>({intervals:n,type:Lc}),Ug=(n={},e)=>{switch(e.type){case Lc:return{...n,...e.intervals};default:return n}},Vg="FETCH_EXTERNAL_COMPONENTS_BEGIN",$c="FETCH_EXTERNAL_COMPONENTS_SUCCESS",jg="FETCH_EXTERNAL_COMPONENTS_FAILURE",Mc=()=>async n=>{n(qg());try{const{data:e}=await wn("internal.php?object=centreon_frontend_component&action=components").get();n(Wg(e))}catch(e){n(Kg(e))}},qg=()=>({type:Vg}),Wg=n=>({data:n,type:$c}),Kg=n=>({error:n,type:jg}),Yg={fetched:!1,hooks:{},pages:{}},Qg=(n=Yg,e)=>{switch(e.type){case $c:return{...n,fetched:!0,hooks:e.data.hooks,pages:e.data.pages};default:return n}},Jg="@tooltip/UPDATE_TOOLTIP",Xg={label:"",toggled:!1,x:0,y:0},Zg=(n=Xg,e)=>{const{data:o}=e;switch(e.type){case Jg:return{...n,...o};default:return n}},n6={fileUploadProgress:{}},e6=(n=n6,e)=>{switch(e.type){case Dc:return{...n,[e.propKey]:e.data};case Ji:return e.data.reset?{...n,fileUploadProgress:{}}:{...n,fileUploadProgress:{...n.fileUploadProgress,...e.data}};default:return n}};var t6=n=>Am({externalComponents:Qg,form:vm,intervals:Ug,navigation:Og,pollerForm:Dg,remoteData:e6,router:zm(n),tooltip:Zg});const Oc=Sm(),Ve=Cm({basename:document.baseURI.replace(window.location.origin,"")}),o6=(n={})=>{const e=[Rm(Ve),Gm,Dm,Oc],i=(typeof window=="object"&&window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__?window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({}):Tm)(Im(...e)),a=Nm(t6(Ve),n,i);return Oc.run(Rg),a},i6=P(()=>({skeletonContainer:{height:"100vh",width:"100%"}})),Xi=()=>{const n=i6();return t.createElement("div",{className:n.skeletonContainer},t.createElement(Kt,{displayHeaderAndNavigation:!0}))};(function(n){const e=n.System.constructor.prototype,o=navigator.userAgent.indexOf("Trident")!==-1;let i,a,r;function s(){let b=0,f;for(const x in n)if(!(!n.hasOwnProperty(x)||!isNaN(x)&&x<n.length||o&&n[x]&&n[x].parent===window)){if(b===0&&x!==i||b===1&&x!==a)return x;b++,f=x}if(f!==r)return f}function l(){i=a=void 0;for(const b in n)!n.hasOwnProperty(b)||!isNaN(b)&&b<n.length||o&&n[b]&&n[b].parent===window||(i?a||(a=b):i=b,r=b);return r}const p=e.import;e.import=function(b,f){return l(),p.call(this,b,f)};const d=[[],function(){return{}}],{getRegister:m}=e;e.getRegister=function(){const b=m.call(this);if(b)return b;const f=s();if(!f)return d;let x;try{x=n[f]}catch(y){return d}return[[],function(y){return{execute(){y({__useDefault:!0,default:x})}}}]}})(typeof self!="undefined"?self:global);const a6=n=>`$centreonExternalModule$${n.replace(/(^\.?\/)|(\.js)/g,"").replace(/\//g,"$")}`,Bc=({basename:n,file:e})=>new Promise(async(o,i)=>{try{const a=a6(e);if(typeof window[a]!="object"){const r=await window.System.import(n+e);window[a]=r}o(window[a])}catch(a){i(a)}}),Uc=({basename:n,files:e})=>{const o=e.map(i=>Bc({basename:n,file:i}));return Promise.all(o)},Vc=(n,e)=>new Promise(async(o,i)=>{const{js:{commons:a,chunks:r,bundle:s},css:l}=e;if(!s)return console.error(new Error("dynamic import should contains js parameter.")),null;try{l&&l.length>0&&await Hm.fetch({address:n+l}),await Uc({basename:n,files:a}),await Uc({basename:n,files:r});const p=await Bc({basename:n,file:s});return o(p)}catch(p){console.error(p)}}),r6=P(n=>({skeleton:{backgroundColor:jn(n.palette.grey[50],.4),margin:n.spacing(.5,2,1,2)}})),Zt=({width:n=15})=>{const e=qn(),o=r6();return t.createElement($n,{animation:"wave",className:o.skeleton,height:e.spacing(5),width:e.spacing(n)})},c6=({history:n,hooks:e,path:o,...i})=>{const a=n.createHref({hash:"",pathname:"/",search:""});return t.createElement(t.Fragment,null,Object.entries(e).filter(([r])=>r.includes(o)).map(([,r])=>{const s=t.lazy(()=>Vc(a,r));return t.createElement(t.Suspense,{fallback:t.createElement(Zt,{width:29}),key:o},t.createElement(s,{centreonAxios:wn,...i}))}))},s6=t.memo(n=>t.createElement(c6,{...n}),({hooks:n},{hooks:e})=>j(n,e)),l6=({externalComponents:n})=>({hooks:n.hooks});var jc=_n(l6)(xi(s6)),z=`@charset "UTF-8";
/* Colors */
/* Fonts */
.mb-2 {
  margin-bottom: 20px;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.half-opacity {
  opacity: 0.5;
}
.hidden {
  display: none !important;
}
.hidden-input {
  width: 0;
  height: 0;
  position: absolute;
  opacity: 0;
  width: 0px;
  height: 0px;
  top: -100px;
}
@font-face {
  font-family: "Roboto Light";
  src: url("__VITE_ASSET__6f6d7b33__") format("woff2"), url("__VITE_ASSET__2f0e40ac__") format("woff"), url("__VITE_ASSET__a6d343d4__") format("truetype");
}
@font-face {
  font-family: "Roboto Regular";
  src: url("__VITE_ASSET__b11b2aeb__") format("woff2"), url("__VITE_ASSET__91658dab__") format("woff"), url("__VITE_ASSET__79e85140__") format("truetype");
}
@font-face {
  font-family: "Roboto Medium";
  src: url("__VITE_ASSET__48afa2e1__") format("woff2"), url("__VITE_ASSET__96cff21a__") format("woff"), url("__VITE_ASSET__b1b55bae__") format("truetype");
}
@font-face {
  font-family: "Roboto Bold";
  src: url("__VITE_ASSET__2adae71b__") format("woff2"), url("__VITE_ASSET__16e6f826__") format("woff"), url("__VITE_ASSET__37f5abe1__") format("truetype");
}
/* Colors */
/* Fonts */
@font-face {
  font-family: "icomoon";
  src: url("__VITE_ASSET__c944afd1__");
  src: url("__VITE_ASSET__c944afd1__") format("embedded-opentype"), url("__VITE_ASSET__6965599f__") format("truetype"), url("__VITE_ASSET__7c0db1b6__") format("woff"), url("__VITE_ASSET__31838b0e__") format("svg");
  font-weight: normal;
  font-style: normal;
}
[class^=icon-], [class*=" icon-"] {
  /* use !important to prevent issues with browser extensions that change fonts */
  font-family: "icomoon" !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  /* Better Font Rendering =========== */
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.icon-home:before {
  content: "\uE904";
  color: #009f98;
}
.icon-home:hover:before {
  content: "\uE904";
  color: #ffffff;
}
.icon-monitoring:before {
  content: "\uE907";
  color: #88bd23;
}
.icon-monitoring:hover:before {
  content: "\uE907";
  color: #ffffff;
}
.icon-reporting:before {
  content: "\uE909";
  color: #ffa200;
}
.icon-reporting:hover:before {
  content: "\uE909";
  color: #ffffff;
}
.icon-configuration:before {
  content: "\uE902";
  color: #009fe9;
}
.icon-configuration:hover:before {
  content: "\uE902";
  color: #ffffff;
}
.icon-administration:before {
  content: "\uE900";
  color: #2b2d84;
}
.icon-administration:hover:before {
  content: "\uE900";
  color: #ffffff;
}
.icon-poller:before {
  content: "\uE908";
  color: #ffffff;
}
.icon-link:before {
  content: "\uE906";
  color: #232f39;
}
.icon-clock:before {
  content: "\uE901";
  color: #232f39;
}
.icon-database:before {
  content: "\uE903";
  color: #232f39;
}
.icon-hosts:before {
  content: "\uE905";
  color: #ffffff;
}
.icon-services:before {
  content: "\uE90A";
  color: #ffffff;
}
.icon-user:before {
  content: "\uE90B";
  color: #ffffff;
}
.icon-copy:before {
  content: "\uE90D";
  color: #ffa125;
  font-size: 19px;
}
.icon-copied:before {
  content: "\uE90C";
  color: #ffa125;
  font-size: 19px;
}
.iconmoon {
  display: block;
  text-align: center;
}
.iconmoon:before {
  font-size: 24px;
}
/* Colors */
/* Fonts */
.btn {
  padding: 7px 8px;
  min-width: 64px;
  font-size: 0.875rem;
  min-height: 32px;
  transition: background-color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms, box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms, border 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;
  box-sizing: border-box;
  line-height: 1.4em;
  font-family: "Open Sans", Arial, Tahoma, Helvetica, Sans-Serif;
  font-weight: 500;
  border-radius: 3px;
  background-color: transparent;
  padding: 6px;
  cursor: pointer;
}
.btn-green {
  color: #88b917;
  border: 1px solid #597f00;
}
.btn-green:hover {
  color: #ffffff;
  background-color: #88b917;
}
.btn-red {
  color: #e00b3d;
  border: 1px solid #e00b3d;
  text-transform: initial;
}
.btn-red:hover {
  color: #ffffff;
  background-color: #e00b3d;
}
.btn.logout {
  height: 31px;
  border-radius: 16px;
  border: 1px solid #cdcdcd;
  outline: none;
  color: #bcbdc0;
  font-size: 0.625rem;
  min-height: auto;
}
.btn.logout:hover {
  background-color: #cdcdcd;
  color: #000916;
}
.header {
  background-color: #232f39;
  box-sizing: border-box;
}
.header-icons {
  display: flex;
  flex-direction: row;
}
.header .wrap-left-icon.pollers {
  cursor: pointer;
  position: absolute !important;
  left: 20px;
  top: 6px;
}
.header .wrap-left-icon:first-child {
  margin-right: 11px;
}
.header .wrap-left-icon__name {
  display: block;
  font-size: 0.6875rem;
  color: #ffffff;
  font-family: "Roboto Light";
  text-transform: lowercase;
}
.header .wrap-left-icon.round {
  width: 31px;
  height: 31px;
  border-radius: 50%;
  padding-top: 4px;
  box-sizing: border-box;
  margin: 0 6px;
}
.header .wrap-left-icon.round.red {
  background-color: #ed1c24;
}
.header .wrap-left-icon.round.orange {
  background-color: #ff9913;
}
.header .wrap-left-icon.round.green {
  background-color: #87bd23;
}
.header .wrap-left-pollers {
  position: relative;
  box-sizing: border-box;
  padding: 10px 21px 11px 63px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}
.header .wrap-left-pollers.submenu-active {
  background-color: #000915;
}
.header .wrap-left-pollers.submenu-active .submenu {
  display: block;
}
.header .wrap-middle-icon.round {
  font-size: 0.875rem;
  padding: 0px 5px;
  font-family: "Roboto Bold";
  position: relative;
  color: #ffffff;
  overflow: hidden;
  text-align: center;
  min-width: 32px;
  height: 32px;
  border-radius: 50px;
  margin: 0 6px;
  box-sizing: border-box;
  text-decoration: none;
}
.header .wrap-middle-icon.round-big {
  border-radius: 15px;
  width: 47px;
  height: 30px;
}
.header .wrap-middle-icon.round .number {
  font-size: 0.875rem;
  font-family: "Roboto Bold";
  position: relative;
  text-decoration: none;
}
.header .wrap-middle-icon.round .number > span {
  line-height: 33px;
  color: #ffffff;
}
.header .wrap-middle-icon.round.red {
  background-color: #ed1c24;
}
.header .wrap-middle-icon.round.red-bordered {
  border: 2px solid #ed1c24;
}
.header .wrap-middle-icon.round.red-bordered span {
  line-height: 30px;
}
.header .wrap-middle-icon.round.gray-dark {
  background-color: #818185;
}
.header .wrap-middle-icon.round.gray-dark-bordered {
  border: 2px solid #818185;
}
.header .wrap-middle-icon.round.gray-dark-bordered span {
  line-height: 30px;
}
.header .wrap-middle-icon.round.green {
  background-color: #87bd23;
}
.header .wrap-middle-icon.round.green-bordered {
  border: 2px solid #87bd23;
}
.header .wrap-middle-icon.round.green-bordered span {
  line-height: 30px;
}
.header .wrap-middle-icon.round.orange {
  background-color: #ff9913;
}
.header .wrap-middle-icon.round.orange-bordered {
  border: 2px solid #ff9913;
}
.header .wrap-middle-icon.round.orange-bordered span {
  line-height: 30px;
}
.header .wrap-middle-icon.round.gray-light {
  background-color: #cdcdcd;
}
.header .wrap-middle-icon.round.gray-light-bordered {
  border: 2px solid #cdcdcd;
}
.header .wrap-middle-icon.round.gray-light-bordered span {
  line-height: 30px;
}
.header .wrap-middle-icon.round.blue {
  background-color: #2ad1d4;
}
.header .wrap-middle-icon.round.blue .number > span {
  line-height: 33px;
}
.header .wrap-middle-icon.round.blue-bordered {
  border: 2px solid #2ad1d4;
}
.header .wrap-middle-icon.round.blue-bordered .number > span {
  line-height: 30px;
}
.header .wrap-right {
  display: flex;
  justify-content: flex-end;
  flex: 1 0 76%;
}
.header .wrap-right-hosts, .header .wrap-right-services, .header .wrap-right-user {
  position: relative;
  padding: 6px 22px 6px 61px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}
.header .wrap-right-hosts.submenu-active, .header .wrap-right-services.submenu-active, .header .wrap-right-user.submenu-active {
  background-color: #000915;
}
.header .wrap-right-hosts .round {
  margin: 0 4px;
}
.header .wrap-right-hosts > span:nth-child(2) {
  margin-left: 18px;
}
.header .wrap-right-services .custom-icon {
  right: 6px;
}
.header .wrap-right-user {
  padding: 6px 22px 6px 35px;
}
.header .wrap-right-user .iconmoon.icon-user {
  cursor: pointer;
  margin-left: 16px;
}
.header .wrap-right-user .iconmoon.icon-user:before {
  font-size: 2rem;
}
.header .wrap-right-icon {
  cursor: pointer;
  position: absolute !important;
  left: 10px;
  top: 8px;
}
.header .wrap-right-icon:first-child {
  text-align: center;
  position: relative;
  margin-right: 10px;
}
.header .wrap-right-icon.hosts {
  left: 23px;
}
.header .wrap-right-icon__name {
  display: block;
  font-size: 0.6875rem;
  color: #ffffff;
  font-family: "Roboto Light";
  text-transform: lowercase;
}
.header .wrap-right-date, .header .wrap-right-time {
  font-family: "Roboto Light";
  color: #ffffff;
  font-size: 0.8125rem;
  display: block;
  text-align: left;
}
.header .wrap-right-time {
  font-size: 1rem;
}
.header .wrap-right-timestamp {
  display: inline-block;
  vertical-align: middle;
  padding-right: 20px;
}
.header .wrap .submenu-top-button {
  margin: 10px 15px;
}
.header .submenu {
  position: absolute;
  left: 0px;
  background-color: #232f39;
  padding: 10px;
  top: 100%;
  z-index: 92;
  box-sizing: border-box;
  display: none;
  text-align: left;
  width: 100%;
}
.header .submenu.pollers .submenu-item-link {
  border-bottom: 1px solid #d1d2d4;
  padding-left: 16px;
}
.header .submenu-item-link {
  padding: 10px;
  display: block;
  font-family: "Roboto Light";
  color: #ffffff;
  text-decoration: none;
  font-size: 0.8rem;
}
.header .submenu-count {
  float: right;
}
.header .submenu.profile .submenu-user-type {
  display: inline-block;
  width: 51%;
  overflow: hidden;
  text-overflow: ellipsis;
}
.header .submenu.profile .submenu-user-edit {
  font-family: "Roboto Light";
  margin-left: 4px;
  color: #ffffff;
  float: right;
  text-decoration-line: underline;
}
.header .submenu.profile .submenu-user-button {
  font-size: 0.785rem;
  color: #ffa225;
  text-decoration: none;
  font-family: "Roboto Light";
  text-align: left;
  line-height: 31px;
  border-radius: 16px;
  border: 1px solid #ffa125;
  background-color: transparent;
  margin: 0 auto;
  display: block;
  width: 95%;
  outline: none;
  position: relative;
  padding: 5px 27px 5px 10px;
  box-sizing: border-box;
}
.header .submenu.profile .submenu-user-button:hover {
  color: #000916;
  background-color: #ffa125;
}
.header .submenu.profile .submenu-user-button:hover .btn-logout-icon:before, .header .submenu.profile .submenu-user-button:hover .icon-copied:before {
  color: #000915;
}
.header .submenu.profile .submenu-user-button span:first-child {
  line-height: 17px;
  display: block;
}
.header .submenu.profile .btn-logout-icon {
  width: 19px;
  height: 19px;
  position: absolute;
  right: 9px;
  top: 50%;
  transform: translateY(-50%);
}
.header .submenu.profile .submenu-bookmark-link {
  margin: 14px 9px 13px;
  width: 62%;
  border: 1px solid #bcbdc0;
  padding: 7px 6px;
  border-radius: 3px;
  outline: none;
  box-sizing: border-box;
}
.header .submenu.profile .submenu-user-name {
  display: block;
  margin-bottom: 10px;
}
.header .submenu.profile .button-wrap .btn {
  margin-top: 10px;
  float: right;
}
.header .submenu-top-item.errors-running .submenu-top-item-link,
.header .submenu-top-item.errors-running .submenu-top-count {
  display: block;
  padding: 10px;
  font-family: "Roboto Light";
  color: #ffffff;
  font-size: 0.8rem;
  padding-left: 15px;
}
.header .submenu-top-item.errors-running .submenu-top-count {
  padding: 0;
  float: right;
}
.header .custom-icon {
  top: 17px;
  right: -1px;
  width: 7px;
  height: 7px;
  border-radius: 3px;
  background-color: #29d1d3;
  position: absolute;
}
.header .toggle-submenu-arrow {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAABGBAMAAADlWgPgAAAAMFBMVEUAAAD///////////////////////////////////////////////////////////87TQQwAAAAEHRSTlMAOnj/b+tYafA2XvU5QvMG/88vVgAAAEVJREFUeJxjYCAfCEAoJiMI7ZrqAKLYOznbQfTqAwynFzAw8L6FYP4DQLHzFNg2ClAALDxh4QsLb3j4w+IDHj+w+KIIAABXsxFtD260yAAAAABJRU5ErkJggg==") no-repeat center center;
  display: inline-block;
  width: 28px;
  height: 37px;
  background-position: 7px -40px;
  vertical-align: middle;
  cursor: pointer;
  position: absolute;
  right: 0;
  top: 8px;
}
.header .submenu-active .submenu {
  display: block;
}
.header .submenu-active .toggle-submenu-arrow {
  background-position: 7px 8px;
}
.header .dot-colored {
  position: relative;
  padding-left: 15px;
}
.header .dot-colored:before {
  position: absolute;
  content: "";
  left: 0;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  top: 50%;
  transform: translateY(-50%);
}
.header .dot-colored.red:before {
  background-color: #ed1b23;
}
.header .dot-colored.orange:before {
  background-color: #ffa125;
}
.header .dot-colored.gray:before {
  background-color: #818185;
}
.header .dot-colored.gray-light:before {
  background-color: #cdcdcd;
}
.header .dot-colored.green:before {
  background-color: #87bd23;
}
.header .dot-colored.blue:before {
  background-color: #2ad1d4;
}
.FullScreen {
  height: 100%;
  background-color: #ffffff;
}
.submenu-top-item-link {
  display: block;
  padding: 0 !important;
  padding-left: 15px !important;
  font-size: 0.8rem;
  color: #ffffff;
  font-family: "Roboto Light";
  margin-top: 10px;
  padding-right: 25px !important;
  position: relative;
}
.submenu-top-item-link .submenu-top-count {
  position: absolute;
  right: 10px;
  top: 0;
}
.wrapper {
  display: flex;
}
.submenu-toggle {
  display: none;
  position: absolute;
  left: 0px;
  background-color: #232f39;
  padding: 10px;
  top: 100%;
  z-index: 99;
  box-sizing: border-box;
  text-align: left;
  width: 100%;
}
.submenu-toggle-active {
  display: block;
}
.wrap-middle-icon {
  display: flex;
}
.link {
  text-decoration: none;
}`;const qc=(n,e)=>{for(const o of["groups","children"])e[o]&&(n=e[o].reduce(qc,n));return e.is_react===!0?n.push(e.url):e.page&&n.push(e.page),n},p6=n=>n.navigation.items,Zi=go(p6,n=>n.reduce(qc,[])),Wc="60901",Kc=(n,e)=>n&&n.length!==0&&n[e]?n[e].warning?"orange":n[e].critical?"red":"green":"green",d6=n=>e=>{const o=Kc(e,"database"),i=Kc(e,"latency");return t.createElement(t.Fragment,null,t.createElement("span",{className:M(z["wrap-left-icon"],z.round,z[o])},t.createElement("span",{className:M(z.iconmoon,z["icon-database"]),title:n(o==="green"?"OK: all database poller updates are active":"Some database poller updates are not active; check your configuration")})),t.createElement("span",{className:M(z["wrap-left-icon"],z.round,z[i])},t.createElement("span",{className:M(z.iconmoon,z["icon-clock"]),title:n(i==="green"?"OK: no latency detected on your platform":"Latency detected, check configuration for better optimization")})))};class Yc extends t.Component{constructor(){super(...arguments);h(this,"pollerService",wn("internal.php?object=centreon_topcounter&action=pollersListIssues"));h(this,"refreshInterval",null);h(this,"state",{data:null,intervalApplied:!1,toggled:!1});h(this,"getData",()=>{this.pollerService.get().then(({data:e})=>{this.setState({data:e})}).catch(e=>{e.response&&e.response.status===401&&this.setState({data:null})})});h(this,"UNSAFE_componentWillReceiveProps",e=>{const{refreshTime:o}=e,{intervalApplied:i}=this.state;o&&!i&&(this.getData(),this.refreshInterval=setInterval(()=>{this.getData()},o),this.setState({intervalApplied:!0}))});h(this,"toggle",()=>{const{toggled:e}=this.state;this.setState({toggled:!e})});h(this,"handleClick",e=>{!this.poller||this.poller.contains(e.target)||this.setState({toggled:!1})})}componentDidMount(){window.addEventListener("mousedown",this.handleClick,!1)}componentWillUnmount(){window.removeEventListener("mousedown",this.handleClick,!1),clearInterval(this.refreshInterval)}render(){const{data:e,toggled:o}=this.state;if(!e)return t.createElement(Zt,null);const{allowedPages:i,t:a}=this.props,r=i.includes(Wc),s=d6(a)(e.issues);return t.createElement("div",{className:M(z["wrap-left-pollers"],{[z["submenu-active"]]:o})},s,t.createElement("div",{ref:l=>this.poller=l},t.createElement("span",{className:M(z["wrap-left-icon"],z.pollers),onClick:this.toggle},t.createElement("span",{className:M(z.iconmoon,z["icon-poller"])}),t.createElement("span",{className:z["wrap-left-icon__name"]},a("Pollers"))),t.createElement("span",{className:z["toggle-submenu-arrow"],onClick:this.toggle},this.props.children),t.createElement("div",{className:M(z.submenu,z.pollers)},t.createElement("div",{className:z["submenu-inner"]},t.createElement("ul",{className:M(z["submenu-items"],z["list-unstyled"])},t.createElement("li",{className:z["submenu-item"]},t.createElement("span",{className:z["submenu-item-link"]},a("All pollers"),t.createElement("span",{className:z["submenu-count"]},e.total?e.total:"..."))),e.issues?Object.entries(e.issues).map(([l,p])=>{let d="";return l==="database"?d=a("Database updates not active"):l==="stability"?d=a("Pollers not running"):l==="latency"&&(d=a("Latency detected")),t.createElement("li",{className:z["submenu-top-item"],key:l},t.createElement("span",{className:z["submenu-top-item-link"]},d,t.createElement("span",{className:z["submenu-top-count"]},p.total?p.total:"...")),Object.entries(p).map(([m,b])=>b.poller?b.poller.map(x=>{let y="red";return m==="warning"&&(y="orange"),t.createElement("span",{className:z["submenu-top-item-link"],key:x.name,style:{padding:"0px 16px 17px"}},t.createElement("span",{className:M(z["dot-colored"],z[y])},x.name))}):null))}):null,r&&t.createElement(gn,{to:`/main.php?p=${Wc}`},t.createElement("button",{className:M(z.btn,z["btn-big"],z["btn-green"],z["submenu-top-button"]),onClick:this.toggle},a("Configure pollers"))))))))}}const m6=n=>({allowedPages:Zi(n),refreshTime:n.intervals?parseInt(n.intervals.AjaxTimeReloadStatistic)*1e3:!1}),b6={};var x6=me()(_n(m6,b6)(Yc));Yc.propTypes={allowedPages:tn.arrayOf(tn.string).isRequired,refreshTime:tn.oneOfType([tn.number,tn.bool]).isRequired};const f6=P(n=>({dateTime:{color:n.palette.common.white}})),g6=()=>{const n=f6(),e=t.useRef(),[o,i]=t.useState({date:"",time:""}),{format:a,toTime:r}=Pn(),s=()=>{const d=new Date,m=a({date:d,formatString:"LL"}),b=r(d);i({date:m,time:b})};t.useEffect(()=>{s();const d=30*1e3;return e.current=window.setInterval(s,d),()=>{clearInterval(e.current)}},[]);const{date:l,time:p}=o;return t.createElement("div",{className:n.dateTime},t.createElement(V,{variant:"body2"},l),t.createElement(V,{variant:"body1"},p))},Qc="50104";class y6 extends t.Component{constructor(){super(...arguments);h(this,"userService",wn("internal.php?object=centreon_topcounter&action=user"));h(this,"refreshTimeout",null);h(this,"state",{copied:!1,data:null,toggled:!1});h(this,"getData",()=>{this.userService.get().then(({data:e})=>{this.setState({data:e},this.refreshData)}).catch(e=>{e.response&&e.response.status===401&&this.setState({data:null})})});h(this,"refreshData",()=>{clearTimeout(this.refreshTimeout),this.refreshTimeout=setTimeout(()=>{this.getData()},6e4)});h(this,"toggle",()=>{const{toggled:e}=this.state;this.setState({toggled:!e})});h(this,"onCopy",()=>{this.autologinNode.select(),window.document.execCommand("copy"),this.setState({copied:!0})});h(this,"handleClick",e=>{!this.profile||this.profile.contains(e.target)||this.setState({toggled:!1})})}componentDidMount(){window.addEventListener("mousedown",this.handleClick,!1),this.getData()}componentWillUnmount(){window.removeEventListener("mousedown",this.handleClick,!1),clearTimeout(this.refreshTimeout)}render(){const{data:e,toggled:o,copied:i}=this.state;if(!e)return t.createElement(Zt,{width:21});const{allowedPages:a,t:r}=this.props,s=a.includes(Qc),{fullname:l,username:p,autologinkey:d}=e,f=`${window.location.href+(window.location.search?"&":"?")}autologin=1&useralias=${p}&token=${d}`;return t.createElement("div",{className:M(z["wrap-right-user"],{[z["submenu-active"]]:o})},t.createElement(g6,null),t.createElement("div",{ref:x=>this.profile=x},t.createElement("span",{className:M(z.iconmoon,z["icon-user"]),onClick:this.toggle}),t.createElement("div",{className:M(z.submenu,z.profile)},t.createElement("div",{className:z["submenu-inner"]},t.createElement("ul",{className:M(z["submenu-items"],z["list-unstyled"])},t.createElement("li",{className:z["submenu-item"]},t.createElement("span",{className:z["submenu-item-link"]},t.createElement("span",{className:z["submenu-user-name"]},l),t.createElement("span",{className:z["submenu-user-type"]},r("as"),` ${p}`),s&&t.createElement(gn,{className:z["submenu-user-edit"],to:`/main.php?p=${Qc}&o=c`,onClick:this.toggle},r("Edit profile")))),d&&t.createElement(t.Fragment,null,t.createElement("button",{className:z["submenu-user-button"],onClick:this.onCopy},r("Copy autologin link"),t.createElement("span",{className:M(z["btn-logout-icon"],z[i?"icon-copied":"icon-copy"])})),t.createElement("textarea",{className:z["hidden-input"],id:"autologin-input",ref:x=>this.autologinNode=x,value:f}))),t.createElement("div",{className:z["button-wrap"]},t.createElement("a",{href:"index.php?disconnect=1"},t.createElement("button",{className:M(z.btn,z["btn-small"],z.logout)},r("Logout"))))))))}}const u6=n=>({allowedPages:Zi(n)}),h6={};var w6=me()(_n(u6,h6)(y6));const _6={name:"resource_types",value:[{id:"host",name:"Host"}]},k6={name:"resource_types",value:[{id:"service",name:"Service"}]},Te=n=>({name:"statuses",value:[n]}),Jc=Te({id:"DOWN",name:"Down"}),Xc=Te({id:"UNREACHABLE",name:"Unreachable"}),Zc=Te({id:"UP",name:"Up"}),ns=Te({id:"PENDING",name:"Pending"}),es=Te({id:"CRITICAL",name:"Critical"}),ts=Te({id:"WARNING",name:"Warning"}),os=Te({id:"UNKNOWN",name:"Unknown"}),is=Te({id:"OK",name:"Ok"}),xe={name:"states",value:[{id:"unhandled_problems",name:"Unhandled"}]},as=({resourceTypeCriterias:n,statusCriterias:e,stateCriterias:o})=>{const i={criterias:[n,e,o,{name:"search",value:""}]};return`/monitoring/resources?filter=${JSON.stringify(i)}&fromTopCounter=true`},Ie=({statusCriterias:n={name:"statuses",value:[]},stateCriterias:e={name:"states",value:[]}}={})=>as({resourceTypeCriterias:_6,stateCriterias:e,statusCriterias:n}),fe=({statusCriterias:n={name:"statuses",value:[]},stateCriterias:e={name:"states",value:[]}}={})=>as({resourceTypeCriterias:k6,stateCriterias:e,statusCriterias:n}),Ne=kr().required().integer(),E6=te().shape({down:te().shape({total:Ne,unhandled:Ne}),ok:Ne,pending:Ne,refreshTime:Ne,total:Ne,unreachable:te().shape({total:Ne,unhandled:Ne})});class rs extends t.Component{constructor(){super(...arguments);h(this,"hostsService",wn("internal.php?object=centreon_topcounter&action=hosts_status"));h(this,"refreshInterval",null);h(this,"state",{data:null,intervalApplied:!1,toggled:!1});h(this,"getData",()=>{this.hostsService.get().then(({data:e})=>{E6.validate(e).then(()=>{this.setState({data:e})})}).catch(e=>{e.response&&e.response.status===401&&this.setState({data:null})})});h(this,"UNSAFE_componentWillReceiveProps",e=>{const{refreshTime:o}=e,{intervalApplied:i}=this.state;o&&!i&&(this.getData(),this.refreshInterval=setInterval(()=>{this.getData()},o),this.setState({intervalApplied:!0}))});h(this,"toggle",()=>{const{toggled:e}=this.state;this.setState({toggled:!e})});h(this,"handleClick",e=>{!this.host||this.host.contains(e.target)||this.setState({toggled:!1})})}componentDidMount(){window.addEventListener("mousedown",this.handleClick,!1)}componentWillUnmount(){window.removeEventListener("mousedown",this.handleClick,!1),clearInterval(this.refreshInterval)}render(){const{data:e,toggled:o}=this.state,{t:i}=this.props;return e?t.createElement("div",{className:`${z.wrapper} wrap-right-hosts`,ref:a=>this.host=a},t.createElement(Tc,{active:o,submenuType:"top"},t.createElement(Cc,{iconName:i("Hosts"),iconType:"hosts",onClick:this.toggle},e.pending>0&&t.createElement("span",{className:z["custom-icon"]})),t.createElement(gn,{className:M(z.link,z["wrap-middle-icon"]),to:Ie({stateCriterias:xe,statusCriterias:Jc})},t.createElement(Oe,{iconColor:"red",iconNumber:t.createElement("span",{id:"count-host-down"},dn(e.down.unhandled).format("0a")),iconType:`${e.down.unhandled>0?"colored":"bordered"}`})),t.createElement(gn,{className:M(z.link,z["wrap-middle-icon"]),to:Ie({stateCriterias:xe,statusCriterias:Xc})},t.createElement(Oe,{iconColor:"gray-dark",iconNumber:t.createElement("span",{id:"count-host-unreachable"},dn(e.unreachable.unhandled).format("0a")),iconType:`${e.unreachable.unhandled>0?"colored":"bordered"}`})),t.createElement(gn,{className:M(z.link,z["wrap-middle-icon"]),to:Ie({statusCriterias:Zc})},t.createElement(Oe,{iconColor:"green",iconNumber:t.createElement("span",{id:"count-host-up"},dn(e.ok).format("0a")),iconType:`${e.ok>0?"colored":"bordered"}`})),t.createElement(Zr,{iconType:"arrow",ref:this.setWrapperRef,rotate:o,onClick:this.toggle}),t.createElement("div",{className:M(z["submenu-toggle"],{[z["submenu-toggle-active"]]:o})},t.createElement(Ic,null,t.createElement(gn,{className:z.link,to:Ie(),onClick:this.toggle},t.createElement(se,{submenuCount:dn(e.total).format(),submenuTitle:i("All")})),t.createElement(gn,{className:z.link,to:Ie({stateCriterias:xe,statusCriterias:Jc}),onClick:this.toggle},t.createElement(se,{dotColored:"red",submenuCount:`${dn(e.down.unhandled).format("0a")}/${dn(e.down.total).format("0a")}`,submenuTitle:i("Down")})),t.createElement(gn,{className:z.link,to:Ie({stateCriterias:xe,statusCriterias:Xc}),onClick:this.toggle},t.createElement(se,{dotColored:"gray",submenuCount:`${dn(e.unreachable.unhandled).format("0a")}/${dn(e.unreachable.total).format("0a")}`,submenuTitle:i("Unreachable")})),t.createElement(gn,{className:z.link,to:Ie({statusCriterias:Zc}),onClick:this.toggle},t.createElement(se,{dotColored:"green",submenuCount:dn(e.ok).format(),submenuTitle:i("Up")})),t.createElement(gn,{className:z.link,to:Ie({statusCriterias:ns}),onClick:this.toggle},t.createElement(se,{dotColored:"blue",submenuCount:dn(e.pending).format(),submenuTitle:i("Pending")})))))):t.createElement(Zt,{width:27})}}const A6=({intervals:n})=>({refreshTime:n?parseInt(n.AjaxTimeReloadMonitoring)*1e3:!1}),v6={};var z6=me()(_n(A6,v6)(rs));rs.propTypes={refreshTime:tn.oneOfType([tn.number,tn.bool]).isRequired};const ge=kr().required().integer(),S6=te().shape({critical:te().shape({total:ge,unhandled:ge}),ok:ge,pending:ge,refreshTime:ge,total:ge,unknown:te().shape({total:ge,unhandled:ge}),warning:te().shape({total:ge,unhandled:ge})});class cs extends t.Component{constructor(){super(...arguments);h(this,"servicesStatusService",wn("internal.php?object=centreon_topcounter&action=servicesStatus"));h(this,"refreshInterval",null);h(this,"state",{data:null,intervalApplied:!1,toggled:!1});h(this,"getData",()=>{this.servicesStatusService.get().then(({data:e})=>{S6.validate(e).then(()=>{this.setState({data:e})})}).catch(e=>{e.response&&e.response.status===401&&this.setState({data:null})})});h(this,"UNSAFE_componentWillReceiveProps",e=>{const{refreshTime:o}=e,{intervalApplied:i}=this.state;o&&!i&&(this.getData(),this.refreshInterval=setInterval(()=>{this.getData()},o),this.setState({intervalApplied:!0}))});h(this,"toggle",()=>{const{toggled:e}=this.state;this.setState({toggled:!e})});h(this,"handleClick",e=>{!this.service||this.service.contains(e.target)||this.setState({toggled:!1})})}componentDidMount(){window.addEventListener("mousedown",this.handleClick,!1)}componentWillUnmount(){window.removeEventListener("mousedown",this.handleClick,!1),clearInterval(this.refreshInterval)}render(){const{data:e,toggled:o}=this.state,{t:i}=this.props;return e?t.createElement("div",{className:`${z.wrapper} wrap-right-services`,ref:a=>this.service=a},t.createElement(Tc,{active:o,submenuType:"top"},t.createElement(Cc,{iconName:i("Services"),iconType:"services",onClick:this.toggle},e.pending>0&&t.createElement("span",{className:z["custom-icon"]})),t.createElement(gn,{className:M(z.link,z["wrap-middle-icon"]),to:fe({stateCriterias:xe,statusCriterias:es})},t.createElement(Oe,{iconColor:"red",iconNumber:t.createElement("span",{id:"count-svc-critical"},dn(e.critical.unhandled).format("0a")),iconType:`${e.critical.unhandled>0?"colored":"bordered"}`})),t.createElement(gn,{className:M(z.link,z["wrap-middle-icon"]),to:fe({stateCriterias:xe,statusCriterias:ts})},t.createElement(Oe,{iconColor:"orange",iconNumber:t.createElement("span",{id:"count-svc-warning"},dn(e.warning.unhandled).format("0a")),iconType:`${e.warning.unhandled>0?"colored":"bordered"}`})),t.createElement(gn,{className:M(z.link,z["wrap-middle-icon"]),to:fe({stateCriterias:xe,statusCriterias:os})},t.createElement(Oe,{iconColor:"gray-light",iconNumber:t.createElement("span",{id:"count-svc-unknown"},dn(e.unknown.unhandled).format("0a")),iconType:`${e.unknown.unhandled>0?"colored":"bordered"}`})),t.createElement(gn,{className:M(z.link,z["wrap-middle-icon"]),to:fe({statusCriterias:is})},t.createElement(Oe,{iconColor:"green",iconNumber:t.createElement("span",{id:"count-svc-ok"},dn(e.ok).format("0a")),iconType:`${e.ok>0?"colored":"bordered"}`})),t.createElement(Zr,{iconType:"arrow",ref:this.setWrapperRef,rotate:o,onClick:this.toggle}),t.createElement("div",{className:M(z["submenu-toggle"],{[z["submenu-toggle-active"]]:o})},t.createElement(Ic,null,t.createElement(gn,{className:z.link,to:fe(),onClick:this.toggle},t.createElement(se,{submenuCount:dn(e.total).format(),submenuTitle:i("All")})),t.createElement(gn,{className:z.link,to:fe({stateCriterias:xe,statusCriterias:es}),onClick:this.toggle},t.createElement(se,{dotColored:"red",submenuCount:`${dn(e.critical.unhandled).format()}/${dn(e.critical.total).format()}`,submenuTitle:i("Critical")})),t.createElement(gn,{className:z.link,to:fe({stateCriterias:xe,statusCriterias:ts}),onClick:this.toggle},t.createElement(se,{dotColored:"orange",submenuCount:`${dn(e.warning.unhandled).format()}/${dn(e.warning.total).format()}`,submenuTitle:i("Warning")})),t.createElement(gn,{className:z.link,to:fe({stateCriterias:xe,statusCriterias:os}),onClick:this.toggle},t.createElement(se,{dotColored:"gray",submenuCount:`${dn(e.unknown.unhandled).format()}/${dn(e.unknown.total).format()}`,submenuTitle:i("Unknown")})),t.createElement(gn,{className:z.link,to:fe({statusCriterias:is}),onClick:this.toggle},t.createElement(se,{dotColored:"green",submenuCount:dn(e.ok).format(),submenuTitle:i("Ok")})),t.createElement(gn,{className:z.link,to:fe({statusCriterias:ns}),onClick:this.toggle},t.createElement(se,{dotColored:"blue",submenuCount:dn(e.pending).format(),submenuTitle:i("Pending")})))))):t.createElement(Zt,{width:33})}}const C6=({intervals:n})=>({refreshTime:n?parseInt(n.AjaxTimeReloadMonitoring)*1e3:!1}),T6={};var I6=me()(_n(C6,T6)(cs));cs.propTypes={refreshTime:tn.oneOfType([tn.number,tn.bool]).isRequired};class N6 extends t.Component{constructor(){super(...arguments);h(this,"refreshIntervalsApi",wn("internal.php?object=centreon_topcounter&action=refreshIntervals"));h(this,"getRefreshIntervals",()=>{const{setRefreshIntervals:e}=this.props;this.refreshIntervalsApi.get().then(({data:o})=>{e(o)}).catch(o=>{console.log(o)})});h(this,"componentDidMount",()=>{this.getRefreshIntervals()})}render(){return t.createElement("header",{className:z.header},t.createElement("div",{className:z["header-icons"]},t.createElement("div",{className:M(z.wrap,z["wrap-left"])},t.createElement(x6,null)),t.createElement("div",{className:M(z.wrap,z["wrap-right"])},t.createElement(jc,{path:"/header/topCounter"}),t.createElement(z6,null),t.createElement(I6,null),t.createElement(w6,null))))}}const R6=()=>({}),G6={setRefreshIntervals:Bg};var D6=_n(R6,G6)(N6);const ss=(n,e)=>{if(e.show===!1)return n;for(const o of["groups","children"])if(e[o])return[...n,{...e,[o]:e[o].reduce(ss,[])}];return[...n,e]},ls=(n,e)=>e.children?[...n,{...e,children:e.children.reduce(ls,[])}]:e.groups?[...n,{...e,groups:e.groups.filter(H6)}]:[...n,e],H6=n=>{if(n.children){for(const e of n.children)if(e.show===!0)return!0}return!1},F6=n=>n.navigation.items,P6=go(F6,n=>n.reduce(ss,[]).reduce(ls,[])),ps=(n,e)=>{for(const o of["groups","children"])e[o]&&(n=e[o].reduce(ps,n));return e.is_react===!0&&(n[e.url]=e.page),n},L6=n=>n.navigation.items,$6=go(L6,n=>n.reduce(ps,{}));class M6 extends t.Component{constructor(){super(...arguments);h(this,"componentDidMount",()=>{const{fetchNavigationData:e}=this.props;e()})}render(){const{navigationData:e,reactRoutes:o}=this.props;return t.createElement(D3,{navigationData:e,reactRoutes:o})}}const O6=n=>({navigationData:P6(n),reactRoutes:$6(n)}),B6=n=>({fetchNavigationData:()=>{n(Pc())},updateTooltip:()=>{n(updateTooltip())}});var U6=_n(O6,B6)(M6),ds=`.tooltip {
  position: absolute;
  background-color: #232f39;
  color: white;
  min-width: 80px;
  z-index: 999;
  padding: 8px;
  font-family: "Open Sans", Arial, Tahoma, Helvetica, sans-serif;
  font-size: 10px;
  border-radius: 3px;
  text-align: center;
  display: block;
  animation: 0.3s fadeIn;
  animation-fill-mode: forwards;
  opacity: 0;
  pointer-events: none;
}
.tooltip.hidden {
  display: none;
}

nav.sidebar.active + .tooltip {
  display: none;
}

@keyframes fadeIn {
  90% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}`;const V6=({x:n,y:e,label:o,toggled:i})=>{const{t:a}=D();return t.createElement("div",{className:M(ds.tooltip,{[ds.hidden]:!i}),style:{left:n,top:e}},a(o))},j6=({tooltip:n})=>({label:n.label,toggled:n.toggled,x:n.x,y:n.y});var q6=_n(j6,null)(V6),Qn=`/* Colors */
/* Fonts */
.mb-2 {
  margin-bottom: 20px;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.half-opacity {
  opacity: 0.5;
}
.hidden {
  display: none !important;
}
.hidden-input {
  width: 0;
  height: 0;
  position: absolute;
  opacity: 0;
  width: 0px;
  height: 0px;
  top: -100px;
}
.footer {
  padding: 0 100px 0 20px;
  box-sizing: border-box;
  background-color: #232f39;
  height: 30px;
  transition: all 0.2s;
  z-index: 92;
}
.footer-wrap {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
}
.footer-wrap span {
  color: #ffffff;
  font-family: "Roboto Light";
  font-size: 0.75rem;
}
.footer-list-item {
  display: inline-block;
  padding-right: 40px;
  position: relative;
  font-family: "Roboto Regular";
  font-size: 0.75rem;
}
.footer-list-item:after {
  position: absolute;
  right: 20px;
  content: "|";
  color: #ffffff;
}
.footer-list-item a {
  text-decoration: none;
  color: #ffffff;
}
.footer-list-item:last-child {
  padding: 0;
}
.footer-list-item:last-child:after {
  content: none;
}
.sidebar.active + .content .footer {
  left: 160px;
}
.full-screen {
  position: fixed;
  right: 10px;
  border-radius: 4px;
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAgBAMAAACMSheAAAAAKlBMVEUACRUAAACAhIoYICv///8wN0Hf4OJQVl4PFyPQ0tRwdXyQlJl1eoClqKz1VaHDAAAADnRSTlP/AP///////////////1Usv+MAAABtSURBVHicY2DAAIyYQqSJKSkpKYNoZiBDASrm4uLiCqJZgQwHqJhbeXkFiOYoLy+DiXkizOKEi3G5QIEXyWJ7uFx8dwPBXhcvng0wN3PB3OKF8AcdxLC5hRK/YQsrbGGKLeyxxREaoL6YICYAABckNTteILVJAAAAAElFTkSuQmCC") no-repeat center center;
  background-size: cover;
  cursor: pointer;
  z-index: 99;
  bottom: 14px;
  width: 38px;
  height: 32px;
  display: inline-block;
}`;class W6 extends t.Component{render(){return t.createElement("footer",{className:Qn.footer},t.createElement("div",{className:Qn["footer-wrap"]},t.createElement("div",{className:Qn["footer-wrap-left"]}),t.createElement("div",{className:Qn["footer-wrap-middle"]},t.createElement("ul",{className:M(Qn["list-unstyled"],Qn["footer-list"])},t.createElement("li",{className:Qn["footer-list-item"]},t.createElement("a",{href:"https://documentation.centreon.com/",rel:"noopener noreferrer",target:"_blank"},"Documentation")),t.createElement("li",{className:Qn["footer-list-item"]},t.createElement("a",{href:"https://support.centreon.com",rel:"noopener noreferrer",target:"_blank"},"Centreon Support")),t.createElement("li",{className:Qn["footer-list-item"]},t.createElement("a",{href:"https://www.centreon.com",rel:"noopener noreferrer",target:"_blank"},"Centreon")),t.createElement("li",{className:Qn["footer-list-item"]},t.createElement("a",{href:"https://github.com/centreon/centreon.git",rel:"noopener noreferrer",target:"_blank"},"Github Project")),t.createElement("li",{className:Qn["footer-list-item"]},t.createElement("a",{href:"https://centreon.github.io",rel:"noopener noreferrer",target:"_blank"},"Slack")))),t.createElement("div",{className:Qn["footer-wrap-right"]},t.createElement("span",null,"Copyright \xA9 2005 - 2020"))))}}class K6 extends t.Component{constructor(e){super(e);h(this,"handleHref",e=>{const{href:o}=e.detail;window.history.pushState(null,null,o)});h(this,"handleDisconnect",e=>{window.location.href=e.detail.href});h(this,"load",()=>{this.setState({loading:!1})});this.mainContainer=null,this.resizeTimeout=null,this.state={loading:!0}}componentDidMount(){this.mainContainer=window.document.getElementById("fullscreen-wrapper"),window.addEventListener("react.href.update",this.handleHref,!1),window.addEventListener("react.href.disconnect",this.handleDisconnect,!1)}componentWillUnmount(){clearTimeout(this.resizeTimeout),window.removeEventListener("resize",this.handleResize),window.removeEventListener("react.href.update",this.handleHref),window.removeEventListener("react.href.disconnect",this.handleDisconnect)}render(){const{loading:e}=this.state,{history:{location:{search:o,hash:i}}}=this.props;let a;return window.fullscreenSearch?a=window.fullscreenSearch+window.fullscreenHash:a=(o||"")+(i||""),t.createElement(t.Fragment,null,e&&t.createElement(Kt,null),t.createElement("iframe",{className:M({[z.hidden]:e}),frameBorder:"0",id:"main-content",scrolling:"yes",src:`./main.get.php${a}`,style:{height:"100%",width:"100%"},title:"Main Content",onLoad:this.load}))}}var v=`/* Colors */
/* Fonts */
.mb-2 {
  margin-bottom: 20px;
}
.list-unstyled {
  list-style: none;
  padding-left: 0;
  margin: 0;
}
.half-opacity {
  opacity: 0.5;
}
.hidden {
  display: none !important;
}
.hidden-input {
  width: 0;
  height: 0;
  position: absolute;
  opacity: 0;
  width: 0px;
  height: 0px;
  top: -100px;
}
.form input, .form select {
  padding: 4px;
  font-size: 11px !important;
  border: 1px solid #929292;
  line-height: 18px;
}
.form-wrapper {
  position: relative;
  max-width: 320px;
  max-height: 590px;
  overflow-y: auto;
  margin-top: 30px;
  margin: 0 auto;
  border: 1px solid #cdcdcd;
  background-color: #f7f7f7;
  padding: 30px;
}
.form-wrapper .invalid-feedback {
  text-align: right;
  padding-top: 7px;
}
.form-wrapper .invalid-feedback .field__msg {
  font-size: 10px;
  font-family: "Roboto Light";
  color: #d0021b;
}
.form-wrapper .css-1aya2g8,
.form-wrapper .css-2o5izw {
  height: auto;
  min-height: 0;
  border-radius: 2px;
  border: 1px solid #cdcdcd;
}
.form-wrapper.installation .form-text {
  margin: 10px 0;
}
.form-status {
  font-family: "Roboto Regular";
}
.form-status.valid {
  color: #acd174;
}
.form-status.failed {
  color: #d0021b;
}
.form-error-message {
  font-size: 12px;
  color: #d0021b;
  display: block;
  text-align: center;
  font-family: "Roboto Regular";
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  font-size: 10px;
  font-family: "Roboto Light";
  color: #242f3a;
  display: block;
  margin-bottom: 5px;
}
.form-group .form-control {
  width: 100%;
  border-radius: 2px;
  border: 1px solid #cdcdcd;
  padding: 4px 10px;
  box-sizing: border-box;
}
.form-group.select label {
  vertical-align: middle;
}
.form-heading {
  margin-bottom: 5px;
}
.form-title {
  font-size: 12px;
  font-family: "Roboto Light";
  color: #009fdf;
}
.form-item {
  padding-bottom: 15px;
}
.form-text {
  font-size: 12px;
  font-family: "Roboto Regular";
  color: #242f3a;
  margin: 20px 0;
}
.form-buttons {
  text-align: right;
  margin-top: 15px;
}
.form-buttons .button {
  border-radius: 4px;
  border: none;
  outline: none;
  box-sizing: border-box;
  cursor: pointer;
  background-color: #009fdf;
  text-decoration: none;
  font-size: 10px;
  color: white;
  font-family: "Roboto Light";
  padding: 5px 33px;
  text-transform: uppercase;
}
.form-buttons .button:hover {
  background: #0072ce;
}
.custom-checkbox label,
.custom-radio label {
  width: auto;
}
.custom-checkbox input[type=radio],
.custom-checkbox input[type=checkbox],
.custom-radio input[type=radio],
.custom-radio input[type=checkbox] {
  vertical-align: top;
}
.custom-control {
  position: relative;
  display: block;
  min-height: 24px;
  padding-left: 15px;
}
.custom-control .form-check-input,
.custom-control .custom-control-input {
  position: absolute;
  z-index: -1;
  opacity: 0;
}
.custom-control .form-check-input:focus ~ .custom-control-label::before,
.custom-control .custom-control-input:focus ~ .custom-control-label::before {
  box-shadow: none !important;
}
.custom-control .custom-control-label {
  padding-left: 5px;
  cursor: pointer;
  margin-bottom: 0;
}
.custom-control .custom-control-label:before {
  position: absolute;
  top: -8.75px;
  left: 0;
  display: block;
  width: 15px !important;
  height: 15px !important;
  content: "";
  top: -1px !important;
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAABQCAMAAACgROw5AAAAtFBMVEUAAAAAn98An98An98An98An98Ant8An98Ant8Ant8Am9wAldUAnt0An98An98Ant4AgNUAgL8An94An98An98An98An98An98An98Ant4Ant0Am9oAmcwAnt4Ant4An98An98Ant8And0Ant4And0An98AnN4AmdsAn90An98AntwAktsAjsYAmd0And4AntsAnt8Ant8Ant4Ant4AgL8Ant4An94Ant8Ant4Ant4Ant8Ai9FyaUhCAAAAPHRSTlMAYK/v/18fz9ZmMwxp1B+RBgiU0I+Av3/XxXEpCnTE3LBueHtw8DYjJcA6BwkPLzJftmTTBLGKjpMfZwvsM23QAAAA9ElEQVR4nO2Vy1ICMRBF75CWYARmhjYoDsj4CDiCT3yh//9fSnBYdWYnCytn1VW51XXTmwNEgiQtRap1EHxv686hOTLdnu4HAirN/JAPVFtM8DFghzQ8AVItdjjNMDorxsXkHPk0ERLlBeyln64srksh4WbguZ9uKtwqIUEL0LbpknBH0o578Kze8eCkHo+wT3WPZ6lHsnoBv5plMRkh60l/Ab/93KOiygLvLAXQd+nCD/mHk2+KNa8+zZfpdngtBzZdSkeuFDtEIpHIn7Mvv2jSjX5h2qAb/EJbGvzym2jwS03YL7sdQb/seoT94gMc/fI/+Ab2nRc82zGRvgAAAABJRU5ErkJggg==") no-repeat;
  background-position: -8px -8px;
  border-radius: 50%;
  pointer-events: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.custom-control .custom-control-label:after {
  position: absolute;
  top: -8.75px;
  left: 0;
  width: 15px !important;
  height: 15px !important;
  content: "";
  display: block;
  border-radius: 50%;
  top: -1px;
  left: 0;
  background-repeat: no-repeat;
  background-position: 50%;
  background-size: 50% 50%;
}
.custom-control.custom-checkbox {
  margin-top: 20px;
}
.custom-radio .form-check-input:checked ~ .custom-control-label:after {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAABQCAMAAACgROw5AAAAtFBMVEUAAAAAn98An98An98An98An98Ant8An98Ant8Ant8Am9wAldUAnt0An98An98Ant4AgNUAgL8An94An98An98An98An98An98An98Ant4Ant0Am9oAmcwAnt4Ant4An98An98Ant8And0Ant4And0An98AnN4AmdsAn90An98AntwAktsAjsYAmd0And4AntsAnt8Ant8Ant4Ant4AgL8Ant4An94Ant8Ant4Ant4Ant8Ai9FyaUhCAAAAPHRSTlMAYK/v/18fz9ZmMwxp1B+RBgiU0I+Av3/XxXEpCnTE3LBueHtw8DYjJcA6BwkPLzJftmTTBLGKjpMfZwvsM23QAAAA9ElEQVR4nO2Vy1ICMRBF75CWYARmhjYoDsj4CDiCT3yh//9fSnBYdWYnCytn1VW51XXTmwNEgiQtRap1EHxv686hOTLdnu4HAirN/JAPVFtM8DFghzQ8AVItdjjNMDorxsXkHPk0ERLlBeyln64srksh4WbguZ9uKtwqIUEL0LbpknBH0o578Kze8eCkHo+wT3WPZ6lHsnoBv5plMRkh60l/Ab/93KOiygLvLAXQd+nCD/mHk2+KNa8+zZfpdngtBzZdSkeuFDtEIpHIn7Mvv2jSjX5h2qAb/EJbGvzym2jwS03YL7sdQb/seoT94gMc/fI/+Ab2nRc82zGRvgAAAABJRU5ErkJggg==") no-repeat;
  background-position: -8px -56px;
}
.custom-radio .form-check-input:checked ~ .custom-control-label:before {
  background: none;
}
.custom-checkbox .custom-control-label:after {
  top: 2px;
}
.custom-checkbox .custom-control-label {
  padding-left: 5px;
}
.custom-checkbox .custom-control-label:before {
  position: absolute;
  top: -1px !important;
  left: -1px;
  display: block;
  width: 15px !important;
  height: 15px !important;
  border-radius: 0;
  content: "";
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB8AAABhAgMAAABT6JIbAAAACVBMVEUAAAAAn98AAACuAvDOAAAAA3RSTlMA/wEeVQk6AAAANUlEQVR4nGNgGHQgNDQ0BMxwYGBQQWU4hoZCGQyMFDFg5mBaAbd9FNAZEBXvVGJgWkHreAcADHcW3Gymm8sAAAAASUVORK5CYII=") no-repeat;
  background-position: -8px -16px;
}
.custom-checkbox .custom-control-label:after {
  position: absolute;
  top: -1px !important;
  left: -1px;
  display: block;
  width: 15px !important;
  height: 15px !important;
  border-radius: 0;
  content: "";
}
.error-block {
  font-size: 10px;
  color: #ed1c24;
  font-family: "Roboto Light";
  text-align: center;
  position: relative;
  margin-top: 26px;
}
.error-block:after {
  content: "";
  width: 23px;
  height: 23px;
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAAA2FBMVEUAAADtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyPtGyNBVOUyAAAASHRSTlMAJwllurwCGccGgmqoPgjNGEafRabJFF8XhGIN/2DLKAEQgSrCXoYxuRIKB/NTj1yaSswROqt/I1KZwxu9O7I1KaBIiarAxDaSEvwAAAAApklEQVR4nL2PRxKCUBBEB4WviCIKmFDBrJhzzun+NxIZLAl/q73ornlvNQB/DhOi8zDLERqPRPmYQBPxBIhJKchTaatkJcBFNQPZHCiyX+QLoBVLIJVFL9c5AyqWAKHq4QZbAxSkzrhFowmOgFbbxTuqaXW3138fg+FXjMb2mHZrk+mHz+a4i6U9q7XDyWaLu9sjOBxxT2ffT5crLn+7e/N4+t//TV6NFg+tAdbBrwAAAABJRU5ErkJggg==") center no-repeat;
  position: absolute;
  top: -20px;
  left: 0;
  right: 0;
  height: 23px;
  max-width: 23px;
  margin: 0 auto;
}
input[type=checkbox]:checked ~ .custom-control-label::after {
  background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB8AAABhAgMAAABT6JIbAAAACVBMVEUAAAAAn98AAACuAvDOAAAAA3RSTlMA/wEeVQk6AAAANUlEQVR4nGNgGHQgNDQ0BMxwYGBQQWU4hoZCGQyMFDFg5mBaAbd9FNAZEBXvVGJgWkHreAcADHcW3Gymm8sAAAAASUVORK5CYII=") no-repeat;
  background-position: -8px -72px;
}
input[type=checkbox]:checked ~ .custom-control-label::before {
  background: none;
}
.css-10odw4q,
.css-1492t68,
.css-10nd86i {
  font-family: "Roboto Regular";
  font-size: 13px;
}`;const Y6=n=>{if(typeof n=="string")return n;const{name:e,type:o,value:i,message:a}=n;if(a)return a;switch(o){case"required":return`${e||"This field"} is required`;case"email":return"Please enter a valid email address";case"maxLength":return Array.isArray(i)?`${e||"Field"} must have at most ${n.maxLength} items`:`${e||"Field"} must be at most ${n.maxLength} characters long`;case"minLength":return Array.isArray(i)?`${e||"Field"} must have at least ${n.minLength} items`:`${e||"Field"} must be at least ${n.minLength} characters long`;case"invalidDate":return`${e||"Field"} is not valid`;default:return`${e||"Field"} is invalid. Reason: ${n.reason}`}},na=({className:n,children:e,isError:o,tagName:i,...a})=>t.createElement(i,{className:M(v.field__msg,v[n],{[v["field__msg--error"]]:!!o}),...a},e);na.defaultProps={className:"",isError:!0,tagName:"div"};let Q6=0;const J6=()=>++Q6,je=n=>{class e extends t.Component{constructor(i){super(i);this.state={isFocused:!1},["getId","handleFocus","handleBlur","isInputValue","renderError"].map(a=>this[a]=this[a].bind(this))}handleFocus(i){const{input:{onFocus:a}}=this.props;this.setState({isFocused:!0}),a&&a(i)}handleBlur(){const{input:{onBlur:i,value:a}}=this.props;if(this.setState({isFocused:!1}),i)return i(a)}getId(){const{name:i}=this.props.input;return this.fieldId||(this.fieldId=J6()),`field-${i}-${this.fieldId}`}isInputValue(i){return i!=null&&i!==""}renderError(){const{meta:{touched:i,error:a}}=this.props;return i&&a?t.createElement(na,null,Y6(a)):null}render(){const{isFocused:i}=this.state,{input:a,meta:r,label:s,autoComplete:l,...p}=this.props,d=l==="off"?{autoComplete:this.getId()}:{};return t.createElement(n,{className:M(v.field,{[v["has-danger"]]:r.invalid&&r.touched},{[v["has-value"]]:this.isInputValue(a.value)},{[v["has-focus"]]:i}),...a,...p,...d,error:this.renderError(),id:this.getId(),label:s,onBlur:this.handleBlur,onFocus:this.handleFocus})}}return e.displayName=`FieldHoc(${n.displayName})`,e.propTypes={input:tn.object.isRequired,label:tn.string,meta:tn.object.isRequired,onBlur:tn.func,onFocus:tn.func},e},no=Yn(["type","name","value","checked","disabled","id","placeholder","autoComplete","autoFocus","multiple","required","step","max","min","rows","pattern","maxlength","onFocus","onChange","onInput","onBlur","onClick","style","defaultValue","readonly"]),Po=({type:n,label:e,placeholder:o,error:i,topRightLabel:a,modifiers:r,renderMeta:s,...l})=>t.createElement("div",{className:M(v["form-group"],{[v["has-danger"]]:!!i})},t.createElement("label",null,t.createElement("span",null,e),t.createElement("span",{className:M(v["label-option"],v.required)},a||null)),t.createElement("input",{className:M(v["form-control"],{[v["is-invalid"]]:!!i}),placeholder:o,type:n,...no(l)}),i?t.createElement("div",{className:v["invalid-feedback"]},i):null);Po.displayName="InputField",Po.defaultProps={className:v["form-control"],modifiers:[],renderMeta:null},Po.propTypes={error:tn.element};var Gn=je(Po);const ea=({checked:n,error:e,label:o,info:i,className:a,...r})=>t.createElement("div",{className:M(v["custom-control"],v["custom-radio"],v["form-group"])},t.createElement("input",{info:!0,"aria-checked":n,checked:n,className:v["form-check-input"],type:"radio",...no(r)}),t.createElement("label",{className:v["custom-control-label"],htmlFor:r.id},o,i),e?t.createElement("div",{className:v["invalid-feedback"]},t.createElement("div",{className:M(v.field__msg,v["field__msg--error"])},e)):null);ea.displayName="RadioField",ea.defaultProps={className:v.field};var Lo=je(ea);const ms=(n,e)=>{if(typeof n=="string")return t.createElement("option",{key:e,value:n},n);if(n.subOptions){const{subOptions:r,text:s,value:l,...p}=n;return t.createElement("optgroup",{key:`optgroup-${e}`,label:s,value:l,...p},r.map((d,m)=>ms(d,`${e}-${m}`)))}const{text:o,value:i,...a}=n;return t.createElement("option",{key:e,value:i,...a},o)},$o=({className:n,defaultOption:e,error:o,label:i,modifiers:a,options:r,styleOverride:s,topRightLabel:l,...p})=>{const[d,m,b]=e||[null,"",!0];return t.createElement("div",{className:M(v["form-group"],v.select),style:s},i?t.createElement("label",null,t.createElement("span",null,i),t.createElement("span",{className:M(v["label-option"],v.optional)},l||null)):null,t.createElement("select",{className:M(v["form-control"],v["custom-select"]),...no(p)},e!==!1?t.createElement("option",{disabled:b,value:d},m):null,r.map(ms)),o&&l==="Required"?t.createElement("div",{className:v["invalid-feedback"]},o):null)};$o.displayName="SelectField",$o.propTypes={defaultOption:tn.oneOfType([tn.array,tn.bool]),error:tn.element,options:tn.array},$o.defaultProps={className:v.field,defaultOption:!1,modifiers:[],options:[],styleOverride:{}};var ta=je($o);const Dn=n=>e=>Fm([E,kn])(e)?n("Required"):void 0;class X6 extends t.Component{constructor(){super(...arguments);h(this,"state",{initialized:!1,inputTypeManual:!0});h(this,"initializeFromRest",e=>{const{change:o}=this.props;o("inputTypeManual",!e),this.setState({initialized:!0,inputTypeManual:!e})});h(this,"UNSAFE_componentWillReceiveProps",e=>{const{waitList:o}=e,{initialized:i}=this.state;o&&!i&&this.initializeFromRest(o.length>0),this.setState({initialized:!0})});h(this,"handleChange",(e,o)=>{const{waitList:i,change:a}=this.props,r=i.find(s=>s.ip===o);a("server_name",r.server_name)})}onManualInputChanged(e){this.setState({inputTypeManual:e})}render(){const{error:e,handleSubmit:o,onSubmit:i,waitList:a,t:r}=this.props,{inputTypeManual:s}=this.state;return t.createElement("div",{className:v["form-wrapper"]},t.createElement("div",{className:v["form-inner"]},t.createElement("div",{className:v["form-heading"]},t.createElement("h2",{className:v["form-title"]},r("Server Configuration"))),t.createElement("form",{autoComplete:"off",onSubmit:o(i)},t.createElement(X,{checked:s,component:Lo,label:r("Create new Poller"),name:"inputTypeManual",onChange:()=>{this.onManualInputChanged(!0)}}),s?t.createElement("div",null,t.createElement(X,{component:Gn,label:`${r("Server Name")}:`,name:"server_name",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Server IP address")}:`,name:"server_ip",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Centreon Central IP address, as seen by this server")}:`,name:"centreon_central_ip",placeholder:"",type:"text",validate:Dn(r)})):null,t.createElement(X,{checked:!s,component:Lo,label:`${r("Select a Poller")}:`,name:"inputTypeManual",onClick:()=>{this.onManualInputChanged(!1)}}),s?null:t.createElement("div",null,a?t.createElement(X,{required:!0,component:ta,label:`${r("Select Pending Poller IP")}:`,name:"server_ip",options:[{disabled:!0,selected:!0,text:r("Select IP"),value:""}].concat(a.map(l=>({text:l.ip,value:l.ip}))),onChange:this.handleChange}):null,t.createElement(X,{component:Gn,label:`${r("Server Name")}:`,name:"server_name",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Server IP address")}:`,name:"server_ip",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Centreon Central IP address, as seen by this server")}:`,name:"centreon_central_ip",placeholder:"",type:"text",validate:Dn(r)})),t.createElement("div",{className:v["form-buttons"]},t.createElement("button",{className:v.button,type:"submit"},r("Next"))),e?t.createElement("div",{className:v["error-block"]},e.message):null)))}}var Z6=me()(Nt({destroyOnUnmount:!1,enableReinitialize:!0,form:"PollerFormStepOne",keepDirtyOnReinitialize:!0})(X6)),qe=`/* Colors */
/* Fonts */
.progress-bar {
  padding-bottom: 39px;
  padding-left: 24px;
  padding-right: 24px;
  background: #ffffff;
  max-width: 890px;
  margin: 0 auto;
}
.progress-bar-items {
  list-style: none;
  padding: 0;
  position: relative;
  display: flex;
  margin-bottom: 0;
  justify-content: space-between;
}
.progress-bar-items:after {
  background-color: #cdcdcd;
  content: "";
  display: block;
  height: 1px;
  position: absolute;
  left: 20px;
  right: 20px;
  top: 17px;
  z-index: 1;
}
.progress-bar-item {
  border-radius: 50%;
  background: #ffffff;
  width: 33px;
  height: 33px;
  z-index: 2;
}
.progress-bar-link {
  border-radius: 50%;
  display: block;
  width: 33px;
  height: 33px;
  line-height: 33px;
  text-align: center;
  border: 1px solid #cdcdcd;
  font-family: "Roboto Light";
  font-size: 0.75rem;
  color: #cdcdcd;
  box-sizing: border-box;
}
.progress-bar-link.active {
  background: #009fdf;
  color: #ffffff;
  border: none;
}
.progress-bar-link.active.prev {
  background: #ffffff;
  color: #009fdf;
  border: 1px solid #009fdf;
}`;class n0 extends t.Component{constructor(){super(...arguments);h(this,"goToPath",e=>{Ve.push(e)})}render(){const{links:e}=this.props;return t.createElement("div",{className:qe["progress-bar"]},t.createElement("div",{className:qe["progress-bar-wrapper"]},t.createElement("ul",{className:qe["progress-bar-items"]},e?e.map(o=>t.createElement("li",{className:qe["progress-bar-item"],key:o.path,onClick:this.goToPath.bind(this,o.path)},t.createElement("span",{className:M(qe["progress-bar-link"],{[qe.active]:o.active},{[qe.prev]:o.prevActive})},o.number))):null)))}}const e0=()=>({}),t0={};var We=_n(e0,t0)(n0);const cn={extensionsManagerPage:"/administration/extensions/manager",notAllowedPage:"/not-allowed",pollerList:"/main.php?p=60901",pollerStep1:"/poller-wizard/5",pollerStep2:"/poller-wizard/6",pollerStep3:"/poller-wizard/7",remoteServerStep1:"/poller-wizard/2",remoteServerStep2:"/poller-wizard/3",remoteServerStep3:"/poller-wizard/4",resources:"/monitoring/resources",serverConfigurationWizard:"/poller-wizard/1"},o0=P(n=>({page:{backgroundColor:n.palette.common.white,padding:n.spacing(2,0)}})),Ke=({children:n})=>{const e=o0();return t.createElement("div",{className:e.page},n)};class i0 extends t.Component{constructor(){super(...arguments);h(this,"links",[{active:!0,number:1,path:cn.serverConfigurationWizard,prevActive:!0},{active:!0,number:2,path:cn.pollerStep1},{active:!1,number:3},{active:!1,number:4}]);h(this,"state",{error:null,waitList:null});h(this,"wizardFormWaitListApi",wn("internal.php?object=centreon_configuration_remote&action=getPollerWaitList"));h(this,"getWaitList",()=>{this.wizardFormWaitListApi.post().then(e=>{this.setState({waitList:e.data})}).catch(()=>{this.setState({waitList:[]})})});h(this,"componentDidMount",()=>{this.getWaitList()});h(this,"handleSubmit",e=>{const{history:o,setPollerWizard:i}=this.props;i(e),o.push(cn.pollerStep2)})}render(){const{links:e}=this,{waitList:o}=this.state;return t.createElement(Ke,null,t.createElement(We,{links:e}),t.createElement(Z6,{initialValues:{},waitList:o,onSubmit:this.handleSubmit.bind(this)}))}}const a0=({pollerForm:n})=>({pollerData:n}),r0={setPollerWizard:Fo};var c0=_n(a0,r0)(i0);const bs=(n,e,o)=>i=>o(i.target.checked?n:e),Mo=({checked:n,className:e,error:o,falseValue:i,fieldMsg:a,label:r,onBlur:s,onChange:l,trueValue:p,value:d,info:m,...b})=>t.createElement("div",{className:M(v["form-group"],{[v["has-danger"]]:!!o})},t.createElement("div",{className:M(v["custom-control"],v["custom-checkbox orange"])},t.createElement("input",{...no(b),"aria-checked":n,checked:d,className:v["custom-control-input"],defaultChecked:d===p,focusin:s&&bs(p,i,s),type:"checkbox",onChange:l&&bs(p,i,l)}),t.createElement("label",{className:v["custom-control-label"],htmlFor:b.id},r,m)),o?t.createElement("div",{className:v["invalid-feedback"]},t.createElement("div",{className:M(v.field__msg,v["field__msg--error"])},o)):null);Mo.displayName="CheckboxField",Mo.propTypes={className:tn.string,error:tn.element,falseValue:tn.any,id:tn.string.isRequired,label:tn.string,trueValue:tn.any,value:tn.bool},Mo.defaultProps={className:v.field,falseValue:!1,trueValue:!0};var eo=je(Mo);class s0 extends t.Component{constructor(){super(...arguments);h(this,"state",{selectedAdditionals:[],selectedMaster:null});h(this,"getAvailableAdditionals",()=>{const{pollers:e}=this.props,{selectedMaster:o}=this.state;return e.filter(a=>{if(a.id!==o)return!0})});h(this,"handleChangeMaster",(e,o)=>{const{change:i}=this.props,{selectedAdditionals:a}=this.state,r=o?a.filter(s=>{if(s.value!==o)return!0}):[];i("linked_remote_slaves",r),this.setState({selectedAdditionals:r,selectedMaster:o})});h(this,"handleChangeAdditionals",(e,o)=>{this.setState({selectedAdditionals:o})})}render(){const{error:e,handleSubmit:o,onSubmit:i,pollers:a,t:r}=this.props,{selectedMaster:s}=this.state,l=this.getAvailableAdditionals();return t.createElement("div",{className:v["form-wrapper"]},t.createElement("div",{className:v["form-inner"]},t.createElement("form",{autoComplete:"off",onSubmit:o(i)},a.length?t.createElement(t.Fragment,null,t.createElement("h2",{className:v["form-title"]},r("Attach poller to a master remote server")),t.createElement(X,{component:ta,name:"linked_remote_master",options:[{text:"",value:null}].concat(a.map(p=>({label:p.name,text:p.name,value:p.id}))),value:s,onChange:this.handleChangeMaster})):null,s&&a.length>=2?t.createElement(t.Fragment,null,t.createElement("h2",{className:v["form-title"]},r("Attach poller to additional remote servers")),t.createElement("div",{className:v["form-item"]},t.createElement(X,{isMulti:!0,component:je(Er),name:"linked_remote_slaves",options:l.map(p=>({label:p.name,value:p.id})),onChange:this.handleChangeAdditionals}))):null,t.createElement(X,{component:eo,defaultValue:!1,label:r("Advanced: reverse Centreon Broker communication flow"),name:"open_broker_flow"}),t.createElement("div",{className:v["form-buttons"]},t.createElement("button",{className:v.button,type:"submit"},r("Apply"))),e?t.createElement("div",{className:v["error-block"]},e.message):null)))}}var l0=me()(Nt({destroyOnUnmount:!1,enableReinitialize:!0,form:"PollerFormStepTwo",keepDirtyOnReinitialize:!0})(s0));class p0 extends t.Component{constructor(){super(...arguments);h(this,"state",{pollers:[]});h(this,"links",[{active:!0,number:1,path:cn.serverConfigurationWizard,prevActive:!0},{active:!0,number:2,path:cn.pollerStep1,prevActive:!0},{active:!0,number:3},{active:!1,number:4}]);h(this,"pollerListApi",wn("internal.php?object=centreon_configuration_remote&action=getRemotesList"));h(this,"wizardFormApi",wn("internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer"));h(this,"getPollers",()=>{this.pollerListApi.post().then(e=>{this.setState({pollers:e.data})})});h(this,"componentDidMount",()=>{this.getPollers()});h(this,"handleSubmit",e=>{const{history:o,pollerData:i,setPollerWizard:a}=this.props,r={...e,...i};return r.server_type="poller",this.wizardFormApi.post("",r).then(s=>{a({submitStatus:s.data.success}),i.linked_remote_master?o.push(cn.pollerStep3):o.push(cn.pollerList)}).catch(s=>{throw new Ar({_error:new Error(s.response.data)})})})}render(){const{links:e}=this,{pollerData:o}=this.props,{pollers:i}=this.state;return t.createElement(Ke,null,t.createElement(We,{links:e}),t.createElement(l0,{initialValues:o,pollers:i,onSubmit:this.handleSubmit.bind(this)}))}}const d0=({pollerForm:n})=>({pollerData:n}),m0={setPollerWizard:Fo};var b0=_n(d0,m0)(p0),xs=({formTitle:n,statusCreating:e,statusGenerating:o,error:i})=>{const{t:a}=D(),r=e===null||o===null,s=(e===!1||o===!1)&&i;return t.createElement("div",{className:M(v["form-wrapper"],v.installation)},t.createElement("div",{className:v["form-inner"]},t.createElement("div",{className:v["form-heading"]},t.createElement("h2",{className:v["form-title"]},n)),t.createElement("p",{className:v["form-text"]},a("Creating Export Task"),t.createElement(Yt,{alignCenter:!0,loading:r},t.createElement(t.Fragment,null,t.createElement("span",{className:M(v["form-status"],v[e?"valid":"failed"])},e!=null?t.createElement("span",null,e?"[OK]":"[FAIL]"):"...")))),t.createElement("p",{className:v["form-text"]},a("Generating Export Files"),t.createElement(Yt,{alignCenter:!0,loading:r},t.createElement(t.Fragment,null,t.createElement("span",{className:M(v["form-status"],v[o?"valid":"failed"])},o!=null?t.createElement("span",null,o?"[OK]":"[FAIL]"):"...")))),s&&t.createElement("span",{className:v["form-error-message"]},i)))};class x0 extends t.Component{constructor(){super(...arguments);h(this,"state",{generateStatus:null});h(this,"links",[{active:!0,number:1,prevActive:!0},{active:!0,number:2,prevActive:!0},{active:!0,number:3,prevActive:!0},{active:!0,number:4}])}render(){const{links:e}=this,{pollerData:o,t:i}=this.props,{generateStatus:a}=this.state;return t.createElement(Ke,null,t.createElement(We,{links:e}),t.createElement(xs,{formTitle:`${i("Finalizing Setup")}:`,statusCreating:o.submitStatus,statusGenerating:a}))}}const f0=({pollerForm:n})=>({pollerData:n}),g0={};var y0=me()(_n(f0,g0)(x0));class u0 extends t.Component{constructor(){super(...arguments);h(this,"state",{initialized:!1,inputTypeManual:!0});h(this,"initializeFromRest",e=>{this.props.change("inputTypeManual",!e),this.setState({initialized:!0,inputTypeManual:!e})});h(this,"UNSAFE_componentWillReceiveProps",e=>{const{waitList:o}=e,{initialized:i}=this.state;o&&!i&&this.initializeFromRest(o.length>0),this.setState({centreon_folder:"/centreon/",initialized:!0})});h(this,"handleChange",(e,o)=>{const{waitList:i,change:a}=this.props,r=i.find(s=>s.ip===o);a("server_name",r.server_name)})}onManualInputChanged(e){this.setState({inputTypeManual:e})}render(){const{error:e,handleSubmit:o,onSubmit:i,waitList:a,t:r}=this.props,{inputTypeManual:s}=this.state;return t.createElement("div",{className:v["form-wrapper"]},t.createElement("div",{className:v["form-inner"]},t.createElement("div",{className:v["form-heading"]},t.createElement("h2",{className:M(v["form-title"],v["mb-2"])},r("Remote Server Configuration"))),t.createElement("form",{autoComplete:"off",onSubmit:o(i)},t.createElement(X,{checked:s,component:Lo,label:r("Create new Remote Server"),name:"inputTypeManual",onChange:()=>{this.onManualInputChanged(!0)}}),s?t.createElement("div",null,t.createElement(X,{component:Gn,label:`${r("Server Name")}:`,name:"server_name",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Server IP address")}:`,name:"server_ip",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Database username")}:`,name:"db_user",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Database password")}:`,name:"db_password",placeholder:"",type:"password",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Centreon Central IP address, as seen by this server")}:`,name:"centreon_central_ip",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Centreon Web Folder on Remote")}:`,name:"centreon_folder",placeholder:"/centreon/",type:"text",validate:Dn(r)}),t.createElement(X,{component:eo,label:r("Do not check SSL certificate validation"),name:"no_check_certificate"}),t.createElement(X,{component:eo,label:r("Do not use configured proxy to connect to this server"),name:"no_proxy"})):null,t.createElement(X,{checked:!s,component:Lo,label:`${r("Select a Remote Server")}:`,name:"inputTypeManual",onClick:()=>{this.onManualInputChanged(!1)}}),s?null:t.createElement("div",null,a?t.createElement(X,{required:!0,component:ta,label:`${r("Select Pending Remote Links")}:`,name:"server_ip",options:[{disabled:!0,selected:!0,text:r("Select IP"),value:""}].concat(a.map(l=>({text:l.ip,value:l.ip}))),onChange:this.handleChange}):null,t.createElement(X,{component:Gn,label:`${r("Server Name")}:`,name:"server_name",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Database username")}:`,name:"db_user",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Database password")}:`,name:"db_password",placeholder:"",type:"password",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Centreon Central IP address, as seen by this server")}:`,name:"centreon_central_ip",placeholder:"",type:"text",validate:Dn(r)}),t.createElement(X,{component:Gn,label:`${r("Centreon Web Folder on Remote")}:`,name:"centreon_folder",placeholder:"/centreon/",type:"text",validate:Dn(r)}),t.createElement(X,{component:eo,label:r("Do not check SSL certificate validation"),name:"no_check_certificate"}),t.createElement(X,{component:eo,label:r("Do not use configured proxy to connect to this server"),name:"no_proxy"})),t.createElement("div",{className:v["form-buttons"]},t.createElement("button",{className:v.button,type:"submit"},r("Next"))),e?t.createElement("div",{className:v["error-block"]},e.message):null)))}}var h0=me()(Nt({destroyOnUnmount:!1,enableReinitialize:!0,form:"RemoteServerFormStepOne",keepDirtyOnReinitialize:!0})(u0));class w0 extends t.Component{constructor(){super(...arguments);h(this,"links",[{active:!0,number:1,path:cn.serverConfigurationWizard,prevActive:!0},{active:!0,number:2,path:cn.remoteServerStep1},{active:!1,number:3},{active:!1,number:4}]);h(this,"state",{waitList:null});h(this,"wizardFormWaitListApi",wn("internal.php?object=centreon_configuration_remote&action=getWaitList"));h(this,"getWaitList",()=>{this.wizardFormWaitListApi.post().then(e=>{this.setState({waitList:e.data})}).catch(()=>{this.setState({waitList:[]})})});h(this,"componentDidMount",()=>{this.getWaitList()});h(this,"handleSubmit",e=>{const{history:o,setPollerWizard:i}=this.props;i(e),o.push(cn.remoteServerStep2)})}render(){const{links:e}=this,{pollerData:o}=this.props,{waitList:i}=this.state;return t.createElement(Ke,null,t.createElement(We,{links:e}),t.createElement(h0,{initialValues:{...o,centreon_folder:"/centreon/"},waitList:i,onSubmit:this.handleSubmit.bind(this)}))}}const _0=({pollerForm:n})=>({pollerData:n}),k0={setPollerWizard:Fo};var E0=_n(_0,k0)(w0);class A0 extends t.Component{constructor(){super(...arguments);h(this,"state",{value:[]});h(this,"handleChange",(e,o)=>{this.setState({value:o})})}render(){const{error:e,handleSubmit:o,onSubmit:i,pollers:a,t:r}=this.props,{value:s}=this.state;return t.createElement("div",{className:v["form-wrapper"]},t.createElement("div",{className:v["form-inner"]},t.createElement("div",{className:v["form-heading"]},t.createElement("h2",{className:v["form-title"]},r("Select pollers to be attached to this new Remote Server"))),t.createElement("form",{autoComplete:"off",onSubmit:o(i)},a?t.createElement(X,{isMulti:!0,multi:!0,component:je(Er),label:`${r("Select linked Remote Server")}:`,name:"linked_pollers",options:a.items.map(l=>({label:l.text,value:l.id})),value:s,onChange:this.handleChange}):null,t.createElement("div",{className:v["form-buttons"]},t.createElement("button",{className:v.button,type:"submit"},r("Apply"))),e?t.createElement("div",{className:v["error-block"]},e.message):null)))}}var v0=me()(Nt({destroyOnUnmount:!1,enableReinitialize:!0,form:"RemoteServerFormStepTwo",keepDirtyOnReinitialize:!0})(A0));class z0 extends t.Component{constructor(){super(...arguments);h(this,"state",{pollers:null});h(this,"links",[{active:!0,number:1,path:cn.serverConfigurationWizard,prevActive:!0},{active:!0,number:2,path:cn.remoteServerStep1,prevActive:!0},{active:!0,number:3},{active:!1,number:4}]);h(this,"pollerListApi",wn("internal.php?object=centreon_configuration_poller&action=list"));h(this,"wizardFormApi",wn("internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer"));h(this,"_spliceOutDefaultPoller",e=>{for(let o=0;o<e.items.length;o++)e.items[o].id==="1"&&e.items.splice(o,1);return e});h(this,"_filterOutDefaultPoller",(e,o)=>{o(this._spliceOutDefaultPoller(e))});h(this,"getPollers",()=>{this.pollerListApi.get().then(e=>{this._filterOutDefaultPoller(e.data,o=>{this.setState({pollers:o})})})});h(this,"componentDidMount",()=>{this.getPollers()});h(this,"handleSubmit",e=>{const{history:o,pollerData:i,setPollerWizard:a}=this.props,r={...e,...i};return r.server_type="remote",this.wizardFormApi.post("",r).then(s=>{s.data.success&&s.data.task_id?(a({submitStatus:s.data.success,taskId:s.data.task_id}),o.push(cn.remoteServerStep3)):o.push(cn.pollerList)}).catch(s=>{throw new Ar({_error:new Error(s.response.data)})})})}render(){const{links:e}=this,{pollers:o}=this.state;return t.createElement(Ke,null,t.createElement(We,{links:e}),t.createElement(v0,{pollers:o,onSubmit:this.handleSubmit}))}}const S0=({pollerForm:n})=>({pollerData:n}),C0={setPollerWizard:Fo};var T0=_n(S0,C0)(z0);class I0 extends t.Component{constructor(){super(...arguments);h(this,"state",{error:null,generateStatus:null});h(this,"links",[{active:!0,number:1,prevActive:!0},{active:!0,number:2,prevActive:!0},{active:!0,number:3,prevActive:!0},{active:!0,number:4}]);h(this,"generationTimeout",null);h(this,"remainingGenerationTimeout",30);h(this,"getExportTask",()=>wn("internal.php?object=centreon_task_service&action=getTaskStatus"));h(this,"componentDidMount",()=>{this.setGenerationTimeout()});h(this,"setGenerationTimeout",()=>{this.remainingGenerationTimeout>0?(this.remainingGenerationTimeout--,this.generationTimeout=setTimeout(this.refreshGeneration,1e3)):this.setState({error:"Export generation timeout",generateStatus:!1})});h(this,"refreshGeneration",()=>{const{history:e}=this.props,{taskId:o}=this.props.pollerData;this.getExportTask().post("",{task_id:o}).then(i=>{i.data.success!==!0?this.setState({error:JSON.stringify(i.data),generateStatus:!1}):i.data.status==="completed"?this.setState({generateStatus:!0},()=>{setTimeout(()=>{e.push(cn.pollerList)},2e3)}):this.setGenerationTimeout()}).catch(i=>{this.setState({error:JSON.stringify(i.response.data),generateStatus:!1})})})}render(){const{links:e}=this,{pollerData:o,t:i}=this.props,{generateStatus:a,error:r}=this.state;return t.createElement(Ke,null,t.createElement(We,{links:e}),t.createElement(xs,{error:r,formTitle:`${i("Finalizing Setup")}:`,statusCreating:o.submitStatus,statusGenerating:a}))}}const N0=({pollerForm:n})=>({pollerData:n}),R0={};var G0=me()(_n(N0,R0)(I0));const fs=t.createContext(void 0),Hn=()=>t.useContext(fs),gs="Acknowledged",to="Acknowledge",ys="Acknowledged by",us="Acknowledge services attached to host",D0="at",H0="Active",F0="Add",P0="All",hs="Author",ye="Comment",dt="Cancel",L0="Clear",ws="Critical",$0="Check duration",M0="Command",O0="Copy",B0="Command copied to clipboard",U0="Current notification number",V0="Current state duration",j0="Details",_s="Down",q0="Downtime duration",ks="Duration",W0="Seconds",K0="Minutes",Y0="Hours",Q0="End date must be greater than start date",Es="End time",J0="Entry time",X0="Filter",Z0="No data available for this period",ny="Is this resource flapping?",ey="Show criterias filters",mt="From",As="Start date",vs="End date",ty="Change start date",oy="Change start time",iy="Change end date",ay="Change end time",oa="Fixed",oo="Graph",zs="Host",ry="Host group",Oo="Downtime",Ss="In downtime",cy="1 day",sy="7 days",ly="31 days",py="Last day",dy="Last 7 days",my="Last 31 days",ia="Set downtime",by="Set downtime on",xy="Downtime set by",Cs="Set downtime on services attached to host",Bo="Check",Ts="Last check",fy="Last notification",gy="Last state change",yy="Latency",uy="Less",hy="Next check",Is="No",Ns="Notify",wy="If checked, a notification is sent to the contacts linked to the object to warn that the incident on the resource has been acknowledged",Rs="Ok",_y="Open",ky="Percent state change",aa="Performance data",Ey="Persistent",Ay="Pending",ra="Monitoring server",ue="Required",vy="Resource problems",Gs="Resource",Ds="Search",ca="Status",zy="Sticky",Hs="Tries",Sy="Information",Cy="More",sa="Refresh",Ty="Disable autorefresh",Iy="Enable autorefresh",Ny="Search help",Ry="This search bar will search matching occurences you type, contained in: host name or alias or address or service description",Gy="You can use regular expression syntax to retrieve more relevant results",Dy="It's possible to specify the criteria you precisely want to search, by using the following syntax:",Hy="Some examples:",Fs="will display resources with host name starting by",Fy="will display resources with service description finishing by",Py="will display all resources with host having alias containing",Ly="will display resources having FQDN that doesn't contain",$y="and host alias containing",My="Tips",Oy="get some help while creating your regular expression query at",By="Service",Uy="Services",Vy="Service group",Ps="Start time",Ls="State",jy="State filter",qy="Status information",Wy="Timezone",bt="To",Ky="Acknowledge command sent",Yy="Downtime command sent",Qy="Check command sent",Jy="Unhandled problems",Xy="Unhandled",$s="Unreachable",Ms="Unknown",Os="Up",Bs="Yes",xt="Oops, something went wrong",Us="Warning",Zy="Seems that your Apache configuration is not up-to-date",nu="Contact your Centreon administrator or update the following file",eu="See Documentation",tu="Services will not be affected because you don't have sufficient permission",ou="Hosts will not be affected because you don't have sufficient permission",iu="No results found",au="Save filter",ru="Save as new",Vs="Save",io="New filter",la="Name",cu="Filter created",su="Filter saved",lu="My filters",js="Edit filters",qs="Delete",pu="Delete filter?",du="Filter deleted",mu="Filter updated",bu="Name cannot be empty",xu="Timeline",pa="Event",da="Acknowledgement",fu="by",ma="Notification",gu="Shortcuts",yu="Configure",uu="View logs",hu="View report",Ws="Copy the link to this resource",wu="Resource link copied to the clipboard",ba="Disacknowledge",_u="More actions",Ks="Disacknowledge services attached to hosts",ku="Disacknowledgement command sent",Ys="Submit a status",Eu="Submit",Au="Status submitted",Qs="Output",vu="No resource found",Js="FQDN / Address",Xs="Alias",zu="URL",Su="Switch to graph mode",Cu="Switch to list mode",Tu="Groups",xa="Add a comment",Iu="Comment added",Nu="Display events",Ru="Export to png",Gu="Parent",Du="Select criterias",Hu="Forward",Fu="Backward",Pu="The end date must be greater than the start date",Zs="Graph options",Lu="Min",$u="Max",Mu="Avg",Ou="Compact time period",Bu="Display metric values tooltip",Uu="Action",Vu="Notes",ju="All checks are disabled",qu="Only passive checks are enabled",Wu="Notifications disabled",Ku="Meta service",Yu="Calculation type",Qu="At least one column must be selected",nl=P(n=>({buttonClose:{position:"absolute",right:n.spacing(.5)},tooltip:{backgroundColor:n.palette.common.white,boxShadow:n.shadows[3],color:n.palette.text.primary,fontSize:n.typography.pxToRem(12),maxWidth:500,padding:n.spacing(1,2,1,1)}})),Ju=["h.name","h.alias","h.address","s.description","information"],Xu=({onClose:n})=>{const e=nl(),{t:o}=D();return t.createElement(t.Fragment,null,t.createElement(fi,{className:e.buttonClose,size:"small",onClick:n},t.createElement(Lm,{fontSize:"small"})),t.createElement(vr,{padding:1},t.createElement("p",null,`${o(Ry)}. ${o(Gy)}.`),t.createElement("p",null,`${o(Dy)} ${Ju.join(":, ")}:.`),t.createElement("p",null,o(Hy)),t.createElement("ul",null,t.createElement("li",null,t.createElement("b",null,"h.name:^FR20"),` ${o(Fs)} "FR20"`),t.createElement("li",null,t.createElement("b",null,"s.description:ens192$"),` ${o(Fy)} "ens192"`),t.createElement("li",null,t.createElement("b",null,"h.alias:prod"),` ${o(Py)} "prod"`),t.createElement("li",null,t.createElement("b",null,"h.address:^((?!production).)*$"),` ${o(Ly)} "production"`),t.createElement("li",null,t.createElement("b",null,"h.name:^FR20 h.alias:prod"),` ${o(Fs)} "FR20" ${o($y)} "prod"`)),t.createElement("i",null,t.createElement("b",null,`${o(My)}: `),`${o(Oy)} `,t.createElement(yo,{href:"https://regex101.com",rel:"noopener noreferrer",target:"_blank"},"regex101.com"))))},Zu=()=>{const n=nl(),{t:e}=D(),[o,i]=t.useState(!1),a=()=>{i(!o)},r=()=>{i(!1)};return t.createElement(we,{interactive:!0,classes:{tooltip:n.tooltip},open:o,title:t.createElement(Xu,{onClose:r})},t.createElement(fi,{"aria-label":e(Ny),size:"small",onClick:a},t.createElement(Pm,null)))},fa="./api/beta",ga=`${fa}/monitoring`,Ee=`${ga}/resources`,ao=`${fa}/users/filters/events-view`,n1=n=>lt({baseEndpoint:ao,parameters:n}),e1=n=>()=>ce(n)(n1({limit:100,page:1})),t1=n=>e=>d3(n)({data:e,endpoint:ao}),el=n=>e=>m3(n)({data:e.filter,endpoint:`${ao}/${e.id}`}),o1=n=>e=>p3(n)({data:{order:e.order},endpoint:`${ao}/${e.id}`}),i1=n=>e=>b3(n)(`${ao}/${e.id}`),le=({memoProps:n,Component:e})=>t.memo(e,(o,i)=>j(Yn(n,o),Yn(n,i))),a1=({filter:n,onCreate:e,open:o,onCancel:i})=>{const{t:a}=D(),{sendRequest:r,sending:s}=fn({request:t1}),l=uo({initialValues:{name:""},onSubmit:m=>{r({criterias:n.criterias,name:m.name}).then(e).catch(b=>{l.setFieldError("name",mn(["response","data","message"],b))})},validationSchema:te().shape({name:be().required(ue)})}),p=m=>{m.keyCode===13&&l.submitForm()},d=Rt(ln(l.isValid),ln(l.dirty));return t.createElement($e,{confirmDisabled:d,labelCancel:a(dt),labelConfirm:a(Vs),labelTitle:a(io),open:o,submitting:s,onCancel:i,onConfirm:l.submitForm},t.createElement(ie,{autoFocus:!0,ariaLabel:a(la),error:l.errors.name,label:a(la),value:l.values.name,onChange:l.handleChange("name"),onKeyDown:p}))},r1=P(n=>({save:{alignItems:"center",display:"grid",gridAutoFlow:"column",gridGap:n.spacing(2)}})),c1=({filter:n,updatedFilter:e,setFilter:o,loadCustomFilters:i,customFilters:a,setEditPanelOpen:r,filters:s})=>{const l=r1(),{t:p}=D(),[d,m]=t.useState(null),[b,f]=t.useState(!1),{sendRequest:x,sending:y}=fn({request:el}),{showMessage:g}=ae(),u=B=>{m(B.currentTarget)},A=()=>{m(null)},w=()=>{A(),f(!0)},k=()=>{f(!1)},T=B=>{k(),i().then(()=>{o(B)})},I=B=>{g({message:p(cu),severity:Sn.success}),T(Gt(["order"],B))},C=()=>{x({filter:Gt(["id"],e),id:e.id}).then(B=>{A(),g({message:p(su),severity:Sn.success}),T(Gt(["order"],B))})},N=()=>{r(!0),A()},H=()=>{const B=oe(pn("id",n.id),s);return!j(B,e)},G=n.id==="",R=gi(H(),ln(G)),O=Rt(H(),G);return t.createElement(t.Fragment,null,t.createElement(Rn,{title:p(au),onClick:u},t.createElement(yi,null)),t.createElement(zr,{keepMounted:!0,anchorEl:d,open:Boolean(d),onClose:A},t.createElement(et,{disabled:!O,onClick:w},p(ru)),t.createElement(et,{disabled:!R,onClick:C},t.createElement("div",{className:l.save},t.createElement("span",null,p(Vs)),y&&t.createElement(ui,{size:15}))),t.createElement(et,{disabled:kn(a),onClick:N},p(js))),b&&t.createElement(a1,{open:!0,filter:e,onCancel:k,onCreate:I}))},s1=["filter","updatedFilter","customFilters","filters"],l1=le({Component:c1,memoProps:s1}),p1=()=>{const{filter:n,updatedFilter:e,setFilter:o,loadCustomFilters:i,customFilters:a,setEditPanelOpen:r,filters:s}=Hn();return t.createElement(l1,{customFilters:a,filter:n,filters:s,loadCustomFilters:i,setEditPanelOpen:r,setFilter:o,updatedFilter:e})},d1=()=>t.createElement($n,{height:36,style:{transform:"none"},width:200}),m1="status_severity_code",b1="asc",Uo=({resourceTypes:n=[],states:e=[],statuses:o=[],hostGroups:i=[],serviceGroups:a=[]}={hostGroups:[],resourceTypes:[],serviceGroups:[],states:[],statuses:[]})=>[{name:"resource_types",object_type:null,type:"multi_select",value:n},{name:"states",object_type:null,type:"multi_select",value:e},{name:"statuses",object_type:null,type:"multi_select",value:o},{name:"host_groups",object_type:"host_groups",type:"multi_select",value:i},{name:"service_groups",object_type:"service_groups",type:"multi_select",value:a},{name:"search",object_type:null,type:"text",value:""},{name:"sort",object_type:null,type:"array",value:[m1,b1]}],x1=()=>[...Uo(),{name:"monitoring_servers",object_type:"monitoring_servers",type:"multi_select",value:[]}],f1=`${ga}/hostgroups`,g1=`${ga}/servicegroups`,y1=`${fa}/monitoring/servers`,u1=n=>lt({baseEndpoint:f1,parameters:n}),h1=n=>lt({baseEndpoint:g1,parameters:n}),w1=n=>lt({baseEndpoint:y1,parameters:n}),Bn={CRITICAL:ws,DOWN:_s,OK:Rs,PENDING:Ay,UNKNOWN:Ms,UNREACHABLE:$s,UP:Os,WARNING:Us,acknowledged:gs,host:zs,in_downtime:Ss,metaservice:Ku,service:By,unhandled_problems:Xy},tl="unhandled_problems",ol={id:tl,name:Bn[tl]},_1="acknowledged",k1={id:"acknowledged",name:Bn[_1]},il="in_downtime",E1={id:il,name:Bn[il]},A1=[ol,k1,E1],al="host",v1={id:al,name:Bn[al]},rl="service",z1={id:rl,name:Bn[rl]},cl="metaservice",S1={id:cl,name:Bn[cl]},C1=[v1,z1,S1],sl="OK",T1={id:sl,name:Bn[sl]},ll="UP",I1={id:ll,name:Bn[ll]},pl="WARNING",ya={id:pl,name:Bn[pl]},dl="DOWN",ua={id:dl,name:Bn[dl]},ml="CRITICAL",ha={id:ml,name:Bn[ml]},bl="UNREACHABLE",N1={id:bl,name:Bn[bl]},xl="UNKNOWN",wa={id:xl,name:Bn[xl]},fl="PENDING",R1={id:fl,name:Bn[fl]},G1=[T1,I1,ya,ua,ha,N1,wa,R1],ft={host_groups:{buildAutocompleteEndpoint:u1,label:ry,sortId:3},monitoring_servers:{buildAutocompleteEndpoint:w1,label:ra,sortId:5},resource_types:{label:Gs,options:C1,sortId:0},service_groups:{buildAutocompleteEndpoint:h1,label:Vy,sortId:4},states:{label:Ls,options:A1,sortId:1},statuses:{label:ca,options:G1,sortId:2}},gt={criterias:Uo(),id:"all",name:P0},gl={id:"",name:io},Vo={criterias:Uo({states:[ol],statuses:[ya,ua,ha,wa]}),id:"unhandled_problems",name:Jy},_a={criterias:Uo({statuses:[ya,ua,ha,wa]}),id:"resource_problems",name:vy},yl={all:gt,resource_problems:_a,unhandled_problems:Vo},ul=({id:n})=>E(yl[n]),D1=n=>hi(n),H1=$m(Sr),hl=n=>Om(H1(n),"name"),F1=({filter:n,setFilter:e})=>{const{t:o}=D(),i=W(D1,K(([l,{label:p}])=>({id:l,name:o(p)})))(ft),a=n.criterias.filter(({name:l})=>!E(ft[l])).map(({name:l})=>({id:l,name:o(ft[l].label)})),r=l=>{const{criterias:p}=n,d=K(an("id"),l),m=W(K(an("name")),Mn(g=>E(ft[g])))(n.criterias),b=wi(d,m),f=wi(m,d),x=Mn(hl(f),p),y=x1().filter(hl(b)).map(g=>({...g,value:[]}));e({...n,criterias:[...x,...y],id:"",name:io})},s=()=>{e(gt)};return t.createElement(rc,{icon:t.createElement(Mm,null),options:i,popperPlacement:"bottom-start",title:o(Du),value:a,onChange:r,onReset:s})},P1=()=>{const{filter:n,setFilter:e}=Hn();return re({Component:t.createElement(F1,{filter:n,setFilter:e}),memoProps:[n]})},L1=({name:n,value:e,parentWidth:o,setCriteriaAndNewFilter:i})=>{const{t:a}=D(),r=wl(),s=o<1e3?1:2,l=y=>y.map(g=>({id:g.id,name:a(g.name)})),p=y=>{i({name:n,value:y})},d=y=>y.map(({id:g})=>({id:g,name:Bn[g]})),{label:m,options:b,buildAutocompleteEndpoint:f}=ft[n],x={className:r.field,label:a(m),limitTags:s,openText:`${a(_y)} ${a(m)}`,value:e};if(E(b)){const y=({search:g,page:u})=>f({limit:10,page:u,search:g});return t.createElement(f3,{field:"name",getEndpoint:y,onChange:(g,u)=>{p(u)},...x})}return t.createElement(Pi,{options:l(b),onChange:(y,g)=>{p(d(g))},...x})},$1=({value:n,name:e,parentWidth:o})=>{const{setCriteriaAndNewFilter:i,getMultiSelectCriterias:a,nextSearch:r}=Hn();return re({Component:t.createElement(L1,{name:e,parentWidth:o,setCriteriaAndNewFilter:i,value:n}),memoProps:[n,e,o,a(),r]})},M1=({setFilter:n,setNextSearch:e,criterias:o})=>{const{t:i}=D(),a=()=>{n(gt),e("")};return t.createElement(Dt,null,({width:r})=>t.createElement(L,{container:!0,alignItems:"center",spacing:1},t.createElement(L,{item:!0},t.createElement(P1,null)),o.map(({name:s,value:l})=>t.createElement(L,{item:!0,key:s},t.createElement($1,{name:s,parentWidth:r,value:l}))),t.createElement(L,{item:!0},t.createElement(Ae,{color:"primary",size:"small",onClick:a},i(L0)))))},O1=()=>{const{setFilter:n,setNextSearch:e,getMultiSelectCriterias:o}=Hn(),i=o();return re({Component:t.createElement(M1,{criterias:i,setFilter:n,setNextSearch:e}),memoProps:[i]})},B1=({options:n,selectedOptionId:e,onChange:o,ariaLabel:i,className:a})=>t.createElement($i,{"aria-label":i,className:a,options:n,selectedOptionId:e,onChange:o}),U1=["options","selectedOptionId"],V1=le({Component:B1,memoProps:U1}),wl=P(n=>({criterias:{marginLeft:36},field:{minWidth:155},filterLineLabel:{textAlign:"center",width:60},filterSelect:{width:200},saveFilter:{alignItems:"center",display:"flex",marginRight:n.spacing(1)},searchField:{width:375}})),j1=()=>{const n=wl(),{t:e}=D(),{filter:o,setFilter:i,nextSearch:a,setNextSearch:r,customFilters:s,customFiltersLoading:l,setCriteria:p,setNewFilter:d,filterExpanded:m,toggleFilterExpanded:b}=Hn(),f=[o,a,s,l],x=()=>{p({name:"search",value:a})},y=I=>{I.keyCode===13&&x()},g=I=>{r(I.target.value),d()},u=I=>{const C=I.target.value,N=yl[C]||(s==null?void 0:s.find(pn("id",C)));i(N),r(N.criterias.find(pn("name","search")).value)},A=[Vo,_a,gt].map(({id:I,name:C})=>({id:I,name:e(C)})),w=kn(s)?[]:[{id:"my_filters",name:e(lu),type:"header"},...s],k=[{id:"",name:e(io)},...A,...w],T=oe(pn("id",o.id),k);return t.createElement(vf,{expandLabel:ey,expandableFilters:t.createElement(O1,null),expanded:m,filters:t.createElement(t.Fragment,null,t.createElement("div",{className:n.saveFilter},t.createElement(p1,null)),t.createElement(L,{container:!0,alignItems:"center",spacing:1},t.createElement(L,{item:!0},l?t.createElement(d1,null):t.createElement(V1,{ariaLabel:e(jy),className:n.filterSelect,options:k.map(Yn(["id","name","type"])),selectedOptionId:T?o.id:"",onChange:u})),t.createElement(L,{item:!0},t.createElement(g3,{EndAdornment:Zu,placeholder:e(Ds),value:a||"",onChange:g,onKeyDown:y})),t.createElement(L,{item:!0},t.createElement(Ae,{color:"primary",size:"small",variant:"contained",onClick:x},e(Ds))))),memoProps:f,onExpand:b})},_l=n=>W(_i,Mn(E),kn,ln)(n),jo=n=>t.createElement("div",null," to be loaded vite "),q1=P(n=>({chip:({color:e})=>({height:n.spacing(2.5),width:n.spacing(2.5),...e&&{color:e}})})),kl=({icon:n,color:e})=>{const o=q1({color:e});return t.createElement("div",{className:`${o.chip}`},n)},ka=()=>{const n=qn();return t.createElement(kl,{color:n.palette.action.inDowntime,icon:t.createElement(jo,{fontSize:"small"})})},Ea=()=>{const n=qn();return t.createElement(kl,{color:n.palette.action.acknowledged,icon:t.createElement(ho,{fontSize:"small"})})},W1=P(n=>({content:{padding:n.spacing(1,2,2,2)}})),Aa=({children:n,className:e})=>{const o=W1();return t.createElement(Wn,{className:e},t.createElement("div",{className:o.content},n))},K1=P(n=>{const e=o=>Qi({severityCode:o,theme:n}).backgroundColor;return{card:({severityCode:o})=>({...o&&{borderColor:e(o),borderStyle:"solid",borderWidth:2}}),title:({severityCode:o})=>({...o&&{color:e(o)}})}}),El=({title:n,content:e,severityCode:o})=>{const{t:i}=D(),a=K1({severityCode:o}),[r,s]=t.useState(!1),l=e.split(/\n|\\n/),p=l.slice(0,3),d=W(Bm(3,l.length),Mn(kn))(l),m=()=>{s(!r)},b=(f,x)=>t.createElement(V,{component:"p",key:`${f}-${x}`,variant:"body2"},f);return t.createElement(Aa,{className:a.card},t.createElement(V,{gutterBottom:!0,className:a.title,color:"textSecondary",variant:"subtitle2"},n),p.map(b),r&&d.map(b),d.length>0&&t.createElement(t.Fragment,null,t.createElement(Um,null),t.createElement(Vm,null,t.createElement(Ae,{color:"primary",size:"small",onClick:m},i(r?uy:Cy)))))},Y1=P(n=>({chip:{gridArea:"chip"},comment:{gridArea:"comment"},commentTitle:{gridArea:"comment-title"},container:{display:"grid",gridGap:n.spacing(2),gridTemplateAreas:` 
      'content-title content chip'
      'comment-title comment chip'
      `,gridTemplateColumns:"1fr 2fr auto"},content:{gridArea:"content"},contentTitle:{gridArea:"content-title"}})),Al=n=>t.createElement(V,{component:"p",key:n,variant:"body2"},n),vl=({title:n,contentLines:e,commentLine:o,chip:i})=>{const{t:a}=D(),r=Y1();return t.createElement(jm,{style:{padding:8}},t.createElement("div",{className:r.container},t.createElement(V,{className:r.contentTitle,color:"textSecondary",variant:"subtitle2"},n),t.createElement("div",{className:r.content},e.map(Al)),t.createElement(V,{className:r.commentTitle,color:"textSecondary",variant:"subtitle2"},a(ye)),t.createElement("div",{className:r.comment},Al(o)),t.createElement("div",{className:r.chip},i)))},Q1=P(n=>({active:{color:n.palette.success.main},container:{height:"100%"},title:{display:"flex",gridGap:n.spacing(1)}})),J1=({title:n,line:e,active:o})=>{const i=Q1(),{t:a}=D();return t.createElement(Aa,{className:i.container},t.createElement("div",{className:i.title},t.createElement(V,{gutterBottom:!0,color:"textSecondary",variant:"body1"},n),o&&t.createElement(we,{title:a(H0)},t.createElement(qm,{className:i.active,fontSize:"small"}))),e)},X1=P(n=>({lineText:{fontSize:n.typography.body2.fontSize,overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}})),Un=({line:n})=>{const e=X1();return t.createElement(Dt,null,({width:o})=>t.createElement(V,{component:"div"},t.createElement(vr,{className:e.lineText,lineHeight:1,style:{maxWidth:o||"unset"}},n)))},Z1=({details:n,toDateTime:e,t:o})=>{var i;return[{field:n.fqdn,line:t.createElement(Un,{line:n.fqdn}),title:Js,xs:12},{field:n.alias,line:t.createElement(Un,{line:n.alias}),title:Xs},{field:n.monitoring_server_name,line:t.createElement(Un,{line:n.monitoring_server_name}),title:ra},{field:n.timezone,line:t.createElement(Un,{line:n.timezone}),title:Wy},{field:n.duration,line:t.createElement(Un,{line:`${n.duration} - ${n.tries}`}),title:V0},{field:n.last_status_change,line:t.createElement(Un,{line:e(n.last_status_change)}),title:gy},{active:n.active_checks,field:n.last_check,line:t.createElement(Un,{line:e(n.last_check)}),title:Ts},{active:n.active_checks,field:n.next_check,line:t.createElement(Un,{line:e(n.next_check)}),title:hy},{field:n.execution_time,line:t.createElement(Un,{line:`${n.execution_time} s`}),title:$0},{field:n.latency,line:t.createElement(Un,{line:`${n.latency} s`}),title:yy},{field:n.flapping,line:t.createElement(Un,{line:o(n.flapping?Bs:Is)}),title:ny},{field:n.percent_state_change,line:t.createElement(Un,{line:`${n.percent_state_change}%`}),title:ky},{field:n.last_notification,line:t.createElement(Un,{line:e(n.last_notification)}),title:fy},{field:n.notification_number,line:t.createElement(Un,{line:n.notification_number.toString()}),title:U0},{field:n.calculation_type,line:t.createElement(Un,{line:n.calculation_type}),title:Yu},{field:n.groups,line:t.createElement(L,{container:!0,spacing:1},(i=n.groups)==null?void 0:i.map(a=>t.createElement(L,{item:!0,key:a.name},t.createElement(Cr,{label:a.name})))),title:Tu,xs:12}]},zl=P(n=>({details:{display:"grid",gridRowGap:n.spacing(2)},loadingSkeleton:{display:"grid",gridRowGap:n.spacing(2),gridTemplateRows:"67px"}})),va=Tr($n)(()=>({transform:"none"})),nh=()=>{const n=zl();return t.createElement("div",{className:n.loadingSkeleton},t.createElement(va,{height:"100%"}),t.createElement(va,{height:"100%"}),t.createElement(va,{height:"100%"}))},eh=({details:n})=>{const{t:e}=D(),{toDateTime:o}=Pn(),i=zl(),{showMessage:a}=ae();if(E(n))return t.createElement(nh,null);const r=()=>{try{Gc(n.command_line),a({message:e(B0),severity:Sn.success})}catch(s){a({message:e(xt),severity:Sn.error})}};return t.createElement(Dt,null,({width:s})=>{var l;return t.createElement("div",{className:i.details},t.createElement(El,{content:n.information,severityCode:n.status.severity_code,title:e(qy)}),(l=n.downtimes)==null?void 0:l.map(({start_time:p,end_time:d,comment:m})=>t.createElement(vl,{chip:t.createElement(ka,null),commentLine:m,contentLines:[...[{prefix:e(mt),time:p},{prefix:e(bt),time:d}].map(({prefix:b,time:f})=>`${b} ${o(f)}`)],key:`downtime-${p}-${d}`,title:e(q0)})),n.acknowledgement&&t.createElement(vl,{chip:t.createElement(Ea,null),commentLine:n.acknowledgement.comment,contentLines:[`${n.acknowledgement.author_name} ${e(D0)} ${o(n.acknowledgement.entry_time)}`],title:e(ys)}),t.createElement(L,{container:!0,spacing:1},Z1({details:n,t:e,toDateTime:o}).map(({title:p,field:d,xs:m=6,line:b,active:f})=>{const x=s>600?m/2:m;return!E(d)&&!kn(d)&&t.createElement(L,{item:!0,key:p,xs:x},t.createElement(J1,{active:f,line:b,title:e(p)}))})),n.performance_data&&t.createElement(El,{content:n.performance_data,title:e(aa)}),n.command_line&&t.createElement(Aa,null,t.createElement(V,{gutterBottom:!0,color:"textSecondary",component:"div",variant:"body1"},t.createElement(L,{container:!0,alignItems:"center",spacing:1},t.createElement(L,{item:!0},e(M0)),t.createElement(L,{item:!0},t.createElement(we,{title:O0,onClick:r},t.createElement(fi,{size:"small"},t.createElement(Ir,{color:"primary",fontSize:"small"})))))),t.createElement(V,{variant:"body2"},n.command_line)))})};var yt;(function(n){n.end="end",n.start="start"})(yt||(yt={}));const Sl={dateTimeFormat:Jt,getStart:()=>xn(Date.now()).subtract(24,"hour").toDate(),id:"last_24_h",largeName:py,name:cy,timelineEventsLimit:20},th={dateTimeFormat:Qt,getStart:()=>xn(Date.now()).subtract(7,"day").toDate(),id:"last_7_days",largeName:dy,name:sy,timelineEventsLimit:100},oh={dateTimeFormat:Qt,getStart:()=>xn(Date.now()).subtract(31,"day").toDate(),id:"last_31_days",largeName:my,name:ly,timelineEventsLimit:500},Cl=[Sl,th,oh],za=n=>oe(pn("id",n))(Cl);xn.extend(Wm);const Tl=({defaultSelectedTimePeriodId:n,defaultSelectedCustomTimePeriod:e,defaultGraphOptions:o,details:i,onTimePeriodChange:a})=>{const[r,s]=t.useState(!1),l=ve([[C=>gi(E(C),E(e)),rn(Sl)],[W(E,ln),rn(za(n))],[zn,rn(null)]])(n),[p,d]=t.useState(l),{sending:m}=Hn(),b=C=>({end:new Date(Date.now()),start:new Date((C==null?void 0:C.getStart())||0)}),f=({start:C,end:N})=>{const H=xn.duration(xn(N).diff(xn(C))).asDays(),G=Ht(H,2)?Qt:Jt,R=ve([[Ht(1),rn(20)],[Ht(7),rn(100)],[zn,rn(500)]])(H);return{end:N,start:C,timelineLimit:R,xAxisTickFormat:G}},[x,y]=t.useState(e?f({end:new Date(tt(0,"end",e)),start:new Date(tt(0,"start",e))}):b(l)),g=C=>E(C)?[x.start.toISOString(),x.end.toISOString()]:[C.getStart().toISOString(),new Date(Date.now()).toISOString()],u=({timePeriod:C,startDate:N,endDate:H})=>{if(W(E,ln)(C)){const[G,R]=g(C);return`?start=${G}&end=${R}`}return`?start=${N==null?void 0:N.toISOString()}&end=${H==null?void 0:H.toISOString()}`},[A,w]=t.useState(u(p?{timePeriod:p}:{endDate:x.end,startDate:x.start})),k=C=>{const N=za(C);d(N),a==null||a({graphOptions:o,selectedTimePeriodId:N.id});const H=b(N);y(H);const G=u({timePeriod:N});w(G),s(!1)},T=({date:C,property:N})=>{const H=f({...x,[N]:C});y(H),a==null||a({graphOptions:o,selectedCustomTimePeriod:{end:H.end.toISOString(),start:H.start.toISOString()}}),d(null);const G=u({endDate:H.end,startDate:H.start});w(G),s(!1)},I=C=>{s(!1),y(f(C)),d(null);const{start:N,end:H}=C,G=u({endDate:H,startDate:N});w(G),a==null||a({graphOptions:o,selectedCustomTimePeriod:{end:H.toISOString(),start:N.toISOString()}})};return t.useEffect(()=>{if(E(p)||E(i)||ln(m))return;w(u({timePeriod:p}));const C=b(p);y(C),s(!0)},[m]),t.useEffect(()=>{const C=f({end:new Date(tt(0,"end",e)),start:new Date(tt(0,"start",e))});if(E(e)||j(C.start,x.start)&&j(C.end,x.end))return;y(C);const N=u({endDate:C.end,startDate:C.start});w(N)},[e]),t.useEffect(()=>{if(E(n)||j(n,p==null?void 0:p.id))return;const C=za(n);d(C);const N=u({timePeriod:C});w(N)},[n]),{adjustTimePeriod:I,changeCustomTimePeriod:T,changeSelectedTimePeriod:k,customTimePeriod:x,getIntervalDates:()=>g(p),periodQueryParameters:A,resourceDetailsUpdated:r,selectedTimePeriod:p}};var Re;(function(n){n.displayEvents="displayEvents",n.displayTooltips="displayTooltips"})(Re||(Re={}));const Sa=t.createContext(void 0),Il=()=>t.useContext(Sa),Nl={[Re.displayTooltips]:{id:Re.displayTooltips,label:Bu,value:!1},[Re.displayEvents]:{id:Re.displayEvents,label:Nu,value:!1}},Rl=({graphTabParameters:n,changeTabGraphOptions:e})=>{const[o,i]=t.useState({...Nl,...n==null?void 0:n.graphOptions});return{changeGraphOptions:r=>()=>{const s={...o,[r]:{...o[r],value:!o[r].value}};i(s),e(s)},graphOptions:o}},ih=P(n=>({optionLabel:{justifyContent:"space-between",margin:0},popoverContent:{margin:n.spacing(1,2)}})),ah=()=>{const[n,e]=t.useState(null),{graphOptions:o,changeGraphOptions:i}=Il(),a=ih(),{t:r}=D(),s=d=>{if(E(n)){e(d.currentTarget);return}e(null)},l=()=>e(null),p=_i(o);return t.createElement(t.Fragment,null,t.createElement(Rn,{ariaLabel:r(Zs),size:"small",title:r(Zs),onClick:s},t.createElement(yi,{style:{fontSize:18}})),t.createElement(Nr,{anchorEl:n,anchorOrigin:{horizontal:"center",vertical:"bottom"},open:ln(E(n)),onClose:l},t.createElement(Km,{className:a.popoverContent},p.map(({label:d,value:m,id:b})=>t.createElement(ot,{className:a.optionLabel,control:t.createElement(Ym,{checked:m,color:"primary",size:"small",onChange:i(b)}),key:d,label:r(d),labelPlacement:"start"})))))},Gl=()=>{const{locale:n,timezone:e}=He(),{format:o}=Pn();class i extends Qm{constructor(){super(...arguments);this.isEqual=(r,s)=>r===null&&s===null?!0:xn(r).isSame(xn(s),"minute")}format(r,s){return o({date:r,formatString:s})}date(r){return xn(r).locale(n)}startOfMonth(r){return xn(r.tz()).startOf("month")}getHours(r){return r.locale(n).tz(e).hour()}setHours(r,s){return r.locale(n).tz(e).set("hour",s)}}return i},rh=t.forwardRef((n,e)=>t.createElement(ie,{...n,ref:e,size:"small"})),Dl=({commonPickersProps:n,date:e,minDate:o,maxDate:i,property:a,setDate:r,changeDate:s})=>{const l={TextFieldComponent:rh};return t.createElement(Jm,{...n,...l,hideTabs:!0,inputVariant:"filled",maxDate:i,minDate:o,size:"small",value:e,variant:"inline",onChange:p=>r(new Date((p==null?void 0:p.toDate())||0)),onClose:s({date:e,property:a})})};xn.extend(Xm);const ch=P(n=>({button:{padding:n.spacing(0,.5)},buttonContent:{alignItems:"center",columnGap:`${n.spacing(1)}px`,display:"grid",gridTemplateColumns:"min-content auto"},compactFromTo:{columnGap:`${n.spacing(.5)}px`,display:"grid",grid:"repeat(2, min-content) / min-content auto"},error:{textAlign:"center"},fromTo:{alignItems:"center",columnGap:`${n.spacing(.5)}px`,display:"grid",gridTemplateColumns:"repeat(4, auto)"},minimalFromTo:{display:"grid",gridTemplateRows:"repeat(2, min-content)",rowGap:`${n.spacing(.3)}px`},minimalPickers:{alignItems:"center",columnGap:`${n.spacing(1)}px`,display:"grid",gridTemplateColumns:"min-content auto"},pickerText:{cursor:"pointer",lineHeight:"1.2"},pickers:{alignItems:"center",columnGap:`${n.spacing(.5)}px`,display:"grid",gridTemplateColumns:`minmax(${n.spacing(15)}px, ${n.spacing(17)}px) min-content minmax(${n.spacing(15)}px, ${n.spacing(17)}px)`},popover:{display:"grid",gridTemplateRows:"auto auto auto",justifyItems:"center",padding:n.spacing(1,2),rowGap:`${n.spacing(1)}px`}})),sh=({customTimePeriod:n,acceptDate:e,isCompact:o})=>{const[i,a]=t.useState(null),[r,s]=t.useState(n.start),[l,p]=t.useState(n.end),d=ch(o),{t:m}=D(),{locale:b}=He(),{format:f}=Pn(),x=Gl(),y=({startDate:I,endDate:C})=>xn(I).isSameOrAfter(xn(C),"minute"),g=({property:I,date:C})=>()=>{const N=n[I];Rt(xn(C).isSame(xn(N)),y({endDate:l,startDate:r}))||e({date:C,property:I})};t.useEffect(()=>{gi(xn(n.start).isSame(xn(r),"minute"),xn(n.end).isSame(xn(l),"minute"))||(s(n.start),p(n.end))},[n.start,n.end]);const u=I=>{a(I.currentTarget)},A=()=>{a(null)},w=Boolean(i),k=y({endDate:l,startDate:r}),T={InputProps:{disableUnderline:!0},autoOk:!0,error:void 0,format:pt};return t.createElement(t.Fragment,null,t.createElement(Ae,{"aria-label":m(Ou),className:d.button,color:"primary",variant:"outlined",onClick:u},t.createElement("div",{className:d.buttonContent},t.createElement(Zm,null),t.createElement("div",{className:o?d.compactFromTo:d.fromTo},t.createElement(V,{variant:"caption"},m(mt),":"),t.createElement(V,{variant:"caption"},f({date:n.start,formatString:pt})),t.createElement(V,{variant:"caption"},m(bt),":"),t.createElement(V,{variant:"caption"},f({date:n.end,formatString:pt}))))),t.createElement(Nr,{anchorEl:i,anchorOrigin:{horizontal:"center",vertical:"top"},open:w,transformOrigin:{horizontal:"center",vertical:"top"},onClose:A},t.createElement("div",{className:d.popover},t.createElement(Rr,{locale:b.substring(0,2),utils:x},t.createElement("div",null,t.createElement(V,null,m(mt)),t.createElement("div",{"aria-label":m(As)},t.createElement(Dl,{changeDate:g,commonPickersProps:T,date:r,maxDate:n.end,property:yt.start,setDate:s}))),t.createElement("div",null,t.createElement(V,null,m(bt)),t.createElement("div",{"aria-label":m(vs)},t.createElement(Dl,{changeDate:g,commonPickersProps:T,date:l,minDate:n.start,property:yt.end,setDate:p})))),k&&t.createElement(Ft,{error:!0,className:d.error},m(Pu)))))},lh=P(n=>({button:{fontSize:n.typography.body2.fontSize},buttonGroup:{alignSelf:"center"},header:{alignItems:"center",columnGap:`${n.spacing(2)}px`,display:"grid",gridTemplateColumns:"repeat(3, auto)",justifyContent:"center",padding:n.spacing(1,.5)}})),ph=K(Yn(["id","name","largeName"]),Cl),Hl=({selectedTimePeriodId:n,onChange:e,disabled:o=!1,customTimePeriod:i,changeCustomTimePeriod:a})=>{const{t:r}=D(),s=lh(),l=qn(),p=ph.map(m=>({...m,largeName:r(m.largeName),name:r(m.name)})),d=({property:m,date:b})=>a({date:b,property:m});return t.createElement(Dt,null,({width:m})=>{const b=Gr(m,l.breakpoints.values.sm);return t.createElement(Wn,{className:s.header},t.createElement(nb,{className:s.buttonGroup,color:"primary",component:"span",disabled:o,size:"small"},K(({id:f,name:x,largeName:y})=>t.createElement(we,{key:x,placement:"top",title:y},t.createElement(Ae,{className:s.button,component:"span",variant:n===f?"contained":"outlined",onClick:()=>e(f)},ve([[Dr(l.breakpoints.values.md),rn(y)],[zn,rn(x)]])(m))),p)),t.createElement(sh,{acceptDate:d,customTimePeriod:i,isCompact:b}),t.createElement(ah,null))})},dh=({endpoint:n,parameters:e})=>lt({baseEndpoint:n,parameters:e}),Fl=n=>({endpoint:e,parameters:o})=>ce(n)(dh({endpoint:e,parameters:o})),Pl=_.object({name:_.string,severity_code:_.number},"Status"),Ll={acknowledged:_.optional(_.boolean),active_checks:_.optional(_.boolean),duration:_.optional(_.string),icon:_.optional(_.object({name:_.string,url:_.string},"ResourceIcon")),id:_.number,in_downtime:_.optional(_.boolean),information:_.optional(_.string),last_check:_.optional(_.string),links:_.optional(_.object({endpoints:_.object({acknowledgement:_.optional(_.string),details:_.optional(_.string),downtime:_.optional(_.string),metrics:_.optional(_.string),performance_graph:_.optional(_.string),status_graph:_.optional(_.string),timeline:_.optional(_.string)},"ResourceLinksEndpoints"),externals:_.object({action_url:_.optional(_.string),notes:_.optional(_.object({label:_.optional(_.string),url:_.string},"ResourceLinksExternalNotes"))},"ResourceLinksExternals"),uris:_.object({configuration:_.optional(_.string),logs:_.optional(_.string),reporting:_.optional(_.string)},"ResourceLinksUris")},"ResourceLinks")),name:_.string,notification_enabled:_.optional(_.boolean),passive_checks:_.optional(_.boolean),severity_level:_.optional(_.number),short_type:_.oneOf([_.isExactly("h"),_.isExactly("m"),_.isExactly("s")],"ResourceShortType"),status:_.optional(Pl),tries:_.optional(_.string),type:_.oneOf([_.isExactly("host"),_.isExactly("metaservice"),_.isExactly("service")],"ResourceType"),uuid:_.string};_.object({...Ll,parent:_.optional(_.object(Ll,"ResourceParent"))},"Resource");const mh=n=>_.optional(_.object({name:_.string},n)),bh=_.object({contact:mh("Contact"),content:_.string,date:_.string,endDate:_.optional(_.string),id:_.number,startDate:_.optional(_.string),status:_.optional(Pl),tries:_.optional(_.number),type:_.string},"TimelineEvent",{endDate:"end_date",startDate:"start_date"}),$l=Rc({entityDecoder:bh,entityDecoderName:"TimelineEvent",listingDecoderName:"TimelineEvents"}),xh=({metrics:n,times:e})=>K(o=>({metrics:n,timeTick:o}),e),fh=({timeTick:n,metrics:e},o)=>({timeTick:n,...(()=>Lt((r,{metric:s,data:l})=>({...r,[s]:l[o]}),{},e))()}),gh=n=>{const e=a=>{const r=mn(["global","lower-limit"],n);return E(r)?!0:a>=r},o=({timeTick:a,...r})=>({...Pt(e,r),timeTick:a}),i=ob(K);return W(xh,i(fh),K(o))(n)},yh=({ds_data:n,legend:e,metric:o,unit:i})=>({areaColor:n.ds_color_area,average:n.ds_average,color:n.ds_color_line,display:!0,filled:n.ds_filled,highlight:void 0,invert:n.ds_invert,legend:n.ds_legend,lineColor:n.ds_color_line,max:n.ds_max,metric:o,min:n.ds_min,name:e,stackOrder:j(n.ds_stack,"1")?parseInt(n.ds_order||"0",10):null,transparency:n.ds_transparency,unit:i}),uh=n=>K(yh,n.metrics),Ye=n=>Math.min(...n),Qe=n=>Math.max(...n),qo=n=>new Date(n.timeTick).valueOf(),hh=n=>W(tb,Mn(j("timeTick")))(n),Ml=n=>e=>an(e,n),Wo=n=>W(K(an("unit")),eb)(n),Ca=({lines:n,timeSeries:e,unit:o})=>{const i=a=>K(r=>Ml(r)(a),e);return W(Pt(pn("unit",o)),K(an("metric")),K(i),Hr,Mn(E))(n)},wh=n=>{const e=({timeTick:i})=>i,o=i=>new Date(i);return W(K(e),K(o))(n)},Ol=({lines:n,metric:e})=>oe(pn("metric",e),n),_h=({lines:n,timeSeries:e})=>W(Wo,K(o=>Ca({lines:n,timeSeries:e,unit:o})),Hr)(n),Bl=({lines:n,timeSeries:e})=>{const o=a=>K(r=>Ml(r)(a),e),i=W(K(an("metric")),K(o))(n);return kn(i)||E(i)?[]:i[0].map((a,r)=>Lt((s,l)=>Fr(l[r],s),0,i))},ut=n=>W(Mn(({stackOrder:e})=>E(e)),ki(an("stackOrder")))(n),kh=n=>W(Mn(({invert:e})=>E(e)),ut)(n),Eh=n=>W(Pt(({invert:e})=>E(e)),ut)(n),Ul=({lines:n,unit:e})=>W(ut,wo(pn("unit",e)))(n),Vl=({lines:n,timeSeries:e})=>{const o=K(an("metric"),n);return K(({timeTick:i,...a})=>({...Lt((r,s)=>({...r,[s]:a[s]}),{},o),timeTick:i}),e)},jl=({timeSeries:n,lines:e,yScale:o,xScale:i})=>t.createElement(ib,{curve:ab,data:n,defined:a=>W(K(an("metric")),Ei(r=>W(mn(["data",r]),E,ln)(a)))(e),keys:K(an("metric"),e),x:a=>{var r;return(r=i(qo(a.data)))!=null?r:0},y0:a=>{var r;return(r=o(a[0]))!=null?r:0},y1:a=>{var r;return(r=o(a[1]))!=null?r:0}},({stacks:a,path:r})=>a.map((s,l)=>{const{areaColor:p,transparency:d,lineColor:m,highlight:b}=rb(l,e);return t.createElement("path",{d:r(s)||"",fill:ql({areaColor:p,transparency:d}),key:`stack-${an("key",s)}`,opacity:b===!1?.3:1,stroke:m,strokeWidth:b?2:1})})),Ah=({leftScale:n,rightScale:e})=>{const o=vi(Ye(n.domain()),Ye(e.domain())),i=_o(Qe(n.domain()),Qe(e.domain())),a=vi(Ye(n.range()),Ye(e.range())),r=_o(Qe(n.range()),Qe(e.range()));return Ai({domain:[o,i],nice:!0,range:[r,a]})},ql=({transparency:n,areaColor:e})=>n?jn(e,1-n*.01):void 0,vh=({xScale:n,leftScale:e,rightScale:o,timeSeries:i,lines:a,graphHeight:r})=>{const[,s,l]=Wo(a),p=!E(l),d=ut(a),m=Eh(a),b=Vl({lines:m,timeSeries:i}),f=kh(a),x=Vl({lines:f,timeSeries:i}),y=Ah({leftScale:e,rightScale:o}),g=wi(a,d);return t.createElement(t.Fragment,null,t.createElement(jl,{lines:m,timeSeries:b,xScale:n,yScale:y}),t.createElement(jl,{lines:f,timeSeries:x,xScale:n,yScale:y}),t.createElement(t.Fragment,null,g.map(({metric:u,areaColor:A,transparency:w,lineColor:k,filled:T,unit:I,highlight:C,invert:N})=>{const G=(()=>{const B=p||I!==s?e:o;return N?Ai({domain:B.domain().reverse(),nice:!0,range:B.range().reverse()}):B})(),R={curve:lb,data:i,defined:O=>!E(O[u]),opacity:C===!1?.3:1,stroke:k,strokeWidth:C?2:1,unit:I,x:O=>n(qo(O)),y:O=>{var B;return(B=G(an(u,O)))!=null?B:null}};return T?t.createElement(cb,{fill:ql({areaColor:A,transparency:w}),fillRule:"nonzero",key:u,y0:Math.min(G(0),r),yScale:G,...R}):t.createElement(sb,{key:u,...R})})))},ht=()=>{const{t:n}=D(),{acl:e}=He(),o=({type:w})=>w,i=({resources:w,action:k})=>W(K(o),wo(T=>_e(["actions",T,k],!0)(e)))(w),a=w=>k=>!i({action:w,resources:k}),r=({resources:w,action:k})=>{const T=pn("type","host");return W(pb(T),Mn(kn),oe(a(k)),ko(E,rn(void 0),W(it,o,ko(j("host"),rn(n(ou)),rn(n(tu))))))(w)};return{canAcknowledge:w=>i({action:"acknowledgement",resources:w}),canAcknowledgeServices:()=>_e(["actions","service","acknowledgement"],!0)(e),canCheck:w=>i({action:"check",resources:w}),canComment:w=>i({action:"comment",resources:w}),canDisacknowledge:w=>i({action:"disacknowledgement",resources:w}),canDisacknowledgeServices:()=>_e(["actions","service","disacknowledgement"],!0)(e),canDowntime:w=>i({action:"downtime",resources:w}),canDowntimeServices:()=>_e(["actions","service","downtime"],!0)(e),canSubmitStatus:w=>i({action:"submit_status",resources:w}),getAcknowledgementDeniedTypeAlert:w=>r({action:"acknowledgement",resources:w}),getDisacknowledgementDeniedTypeAlert:w=>r({action:"disacknowledgement",resources:w}),getDowntimeDeniedTypeAlert:w=>r({action:"downtime",resources:w})}},Wl=n=>[{color:n.palette.action.inDowntimeBackground,condition:({in_downtime:e})=>e,name:"inDowntime"},{color:n.palette.action.acknowledgedBackground,condition:({acknowledged:e})=>e,name:"acknowledged"}],zh=P(()=>({name:{cursor:"pointer",overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}})),Kl=({name:n,onSelect:e,variant:o="body1"})=>{const i=zh();return t.createElement(V,{className:i.name,variant:o,onClick:e},n)},Sh=P(n=>({header:({displaySeverity:e})=>({alignItems:"center",display:"grid",gridGap:n.spacing(2),gridTemplateColumns:`${e?"auto":""} auto minmax(0, 1fr) auto`,height:43,padding:n.spacing(0,1)})})),Ch=P(n=>({parent:{alignItems:"center",display:"grid",gridGap:n.spacing(1),gridTemplateColumns:"auto minmax(0, 1fr)"},truncated:{overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}})),Th=()=>t.createElement(L,{container:!0,item:!0,alignItems:"center",spacing:2,style:{flexGrow:1}},t.createElement(L,{item:!0},t.createElement($n,{height:25,variant:"circle",width:25})),t.createElement(L,{item:!0},t.createElement($n,{height:25,width:250}))),Ih=({details:n,onSelectParent:e})=>{var s;const{t:o}=D(),{showMessage:i}=ae(),a=Ch(),r=()=>{try{Gc(window.location.href),i({message:o(wu),severity:Sn.success})}catch(l){i({message:o(xt),severity:Sn.error})}};return n===void 0?t.createElement(Th,null):t.createElement(t.Fragment,null,(n==null?void 0:n.severity_level)&&t.createElement(Ue,{label:n==null?void 0:n.severity_level.toString(),severityCode:Be.None}),t.createElement(Ue,{label:o(n.status.name),severityCode:n.status.severity_code}),t.createElement("div",null,t.createElement(V,{className:a.truncated},n.name),db(["parent","status"],n)&&t.createElement("div",{className:a.parent},t.createElement(Ue,{severityCode:((s=n.parent.status)==null?void 0:s.severity_code)||Be.None}),t.createElement(Kl,{name:n.parent.name,variant:"caption",onSelect:()=>e(n.parent)}))),t.createElement(Rn,{ariaLabel:o(Ws),size:"small",title:o(Ws),onClick:r},t.createElement(Ir,{fontSize:"small"})))},Nh=({details:n,onSelectParent:e})=>{const o=Sh({displaySeverity:ln(E(n==null?void 0:n.severity_level))});return t.createElement("div",{className:o.header},t.createElement(Ih,{details:n,onSelectParent:e}))};var Rh=le({Component:Nh,memoProps:["details"]});const Yl=t.createContext({bottom:0,top:0}),Gh=()=>{var y;const{t:n}=D(),e=qn(),o=t.useRef(),{openDetailsTabId:i,details:a,panelWidth:r,setOpenDetailsTabId:s,clearSelectedResource:l,setPanelWidth:p,selectResource:d}=Hn();t.useEffect(()=>{var u;if(E(a))return;((u=Xo.find(pn("id",i)))==null?void 0:u.getIsActive(a))||s(Et)},[a]);const m=()=>E(a)?Xo:Xo.filter(({getIsActive:g})=>g(a)),b=g=>{const u=zi(pn("id",g),m());return u>0?u:0},f=g=>()=>{s(g)},x=()=>{const{downtimes:g,acknowledgement:u}=a||{},A=Wl(e).find(({condition:w})=>w({acknowledged:!E(u),in_downtime:W(bb([]),kn,ln)(g)}));return E(A)?e.palette.common.white:jn(A.color,.8)};return t.createElement(Yl.Provider,{value:Yn(["top","bottom"],((y=o.current)==null?void 0:y.getBoundingClientRect())||{bottom:0,top:0})},t.createElement(Sf,{header:t.createElement(Rh,{details:a,onSelectParent:d}),headerBackgroundColor:x(),memoProps:[i,a,r],ref:o,selectedTab:t.createElement(f2,{details:a,id:i}),selectedTabId:b(i),tabs:m().map(({id:g,title:u})=>t.createElement(mb,{disabled:E(a),key:g,label:E(a)?t.createElement($n,{width:60}):n(u),style:{minWidth:"unset"},onClick:f(g)})),width:r,onClose:l,onResize:p}))},Dh=`${Ee}/acknowledge`,Hh=`${Ee}/downtime`,Fh=`${Ee}/check`,Ph=`${Ee}/comments`,Lh=n=>({resources:e,params:o})=>he.post(Dh,{acknowledgement:{comment:o.comment,is_notify_contacts:o.notify,with_services:o.acknowledgeAttachedResources},resources:K(Yn(["type","id","parent"]),e)},{cancelToken:n}),$h=n=>({resources:e,params:o})=>he.post(Hh,{downtime:{comment:o.comment,duration:o.duration,end_time:o.endTime,is_fixed:o.fixed,start_time:o.startTime,with_services:o.downtimeAttachedResources},resources:K(Yn(["type","id","parent"]),e)},{cancelToken:n}),Mh=({resources:n,cancelToken:e})=>he.post(Fh,{resources:K(Yn(["type","id","parent"]),n)},{cancelToken:e}),Oh=n=>({resources:e,parameters:o})=>he.post(Ph,{resources:e.map(i=>({...Yn(["id","type","parent"],i),comment:o.comment,date:o.date}))},{cancelToken:n}),Ql=({onClose:n,onSuccess:e,resource:o,date:i})=>{const{t:a}=D(),{toIsoString:r,toDateTime:s}=Pn(),{showMessage:l}=ae(),[p,d]=t.useState(),{sendRequest:m,sending:b}=fn({request:Oh}),f=u=>{d(u.target.value)},x=()=>{const u={comment:p,date:r(i)};m({parameters:u,resources:[o]}).then(()=>{l({message:a(Iu),severity:Sn.success}),e(u)})},y=()=>{if(E(p))return;const u=p||"";return W(xb,kn)(u)?a(ue):void 0},g=E(y())&&!E(p)&&!b;return t.createElement($e,{open:!0,confirmDisabled:!g,labelConfirm:a(F0),labelTitle:a(xa),submitting:b,onCancel:n,onClose:n,onConfirm:x},t.createElement(L,{container:!0,direction:"column",spacing:2},t.createElement(L,{item:!0},t.createElement(V,{variant:"h6"},s(i))),t.createElement(L,{item:!0},t.createElement(ie,{autoFocus:!0,multiline:!0,required:!0,ariaLabel:a(ye),error:y(),label:a(ye),rows:3,style:{width:300},value:p,onChange:f}))))},Jl=t.createContext(void 0),Xl=()=>t.useContext(Jl),Ta=n=>{const e=180;return E(n)?"":fb(n.length,e)?`${n.substring(0,e)}...`:n},Ia=-32,Jn=20,Bh=P(n=>({tooltip:{backgroundColor:"transparent"},tooltipContent:{padding:n.spacing(1)}})),Zl=({icon:n,header:e,event:o,xIcon:i,marker:a,setAnnotationHovered:r})=>{var d;const s=Bh(),{t:l}=D(),p=`${Ta(o.content)} (${l(fu)} ${(d=o.contact)==null?void 0:d.name})`;return t.createElement("g",null,t.createElement(we,{classes:{tooltip:s.tooltip},title:t.createElement(Wn,{className:s.tooltipContent},t.createElement(V,{variant:"body2"},e),t.createElement(V,{variant:"caption"},p))},t.createElement("svg",{height:Jn,width:Jn,x:i,y:Ia,onMouseEnter:()=>r(()=>o),onMouseLeave:()=>r(()=>{})},t.createElement("rect",{fill:"transparent",height:Jn,width:Jn}),n)),a)},Uh=P(n=>({icon:{transition:n.transitions.create("color",{duration:n.transitions.duration.shortest})}})),Vh=({color:n,graphHeight:e,xScale:o,date:i,Icon:a,ariaLabel:r,...s})=>{const{toDateTime:l}=Pn(),p=Uh(),{annotationHovered:d,setAnnotationHovered:m,getStrokeWidth:b,getStrokeOpacity:f,getIconColor:x}=Xl(),y=-Jn/2,g=o(new Date(i)),u=l(i),A=t.createElement(Si,{from:{x:g,y:Ia+Jn+2},stroke:n,strokeOpacity:f(an("event",s)),strokeWidth:b(an("event",s)),to:{x:g,y:e}}),w=t.createElement(a,{"aria-label":r,className:p.icon,height:Jn,style:{color:x({color:n,event:an("event",s)})},width:Jn});return re({Component:t.createElement(Zl,{header:u,icon:w,marker:A,setAnnotationHovered:m,xIcon:g+y,...s}),memoProps:[d,g]})},jh=P(n=>({icon:{transition:n.transitions.create("color",{duration:n.transitions.duration.shortest})}})),qh=({Icon:n,ariaLabel:e,color:o,graphHeight:i,xScale:a,startDate:r,endDate:s,...l})=>{const{toDateTime:p}=Pn(),d=jh(),{annotationHovered:m,setAnnotationHovered:b,getFill:f,getIconColor:x}=Xl(),y=-Jn/2,g=_o(a(new Date(r)),0),u=s?a(new Date(s)):a.range()[1],A=t.createElement(Ci,{fill:f({color:o,event:an("event",l)}),height:i+Jn/2,width:u-g,x:g,y:Ia+Jn+2,onMouseEnter:()=>b(()=>an("event",l)),onMouseLeave:()=>b(()=>{})}),w=`${mt} ${p(r)}`,k=s?` ${bt} ${p(s)}`:"",T=`${w}${k}`,I=t.createElement(n,{"aria-label":e,className:d.icon,height:Jn,style:{color:x({color:o,event:an("event",l)})},width:Jn});return re({Component:t.createElement(Zl,{header:T,icon:I,marker:A,setAnnotationHovered:b,xIcon:g+(u-g)/2+y,...l}),memoProps:[m,g,u]})},Na=({type:n,xScale:e,timeline:o,graphHeight:i,Icon:a,ariaLabel:r,color:s})=>{const l=Pt(pn("type",n),o);return t.createElement(t.Fragment,null,l.map(p=>{const d={Icon:a,ariaLabel:r,color:s,event:p,graphHeight:i,xScale:e};return E(p.startDate)&&E(p.endDate)?t.createElement(Vh,{date:p.date,key:p.id,...d}):t.createElement(qh,{endDate:p.endDate,key:p.id,startDate:p.startDate,...d})}))},Wh=n=>{const{t:e}=D(),o=qn();return t.createElement(Na,{Icon:Pr,ariaLabel:e(ye),color:o.palette.primary.main,type:"comment",...n})},Kh=n=>{const{t:e}=D(),i=qn().palette.action.acknowledged;return t.createElement(Na,{Icon:ho,ariaLabel:e(da),color:i,type:"acknowledgement",...n})},Yh=n=>{const{t:e}=D(),i=qn().palette.action.inDowntime;return t.createElement(Na,{Icon:jo,ariaLabel:e(Oo),color:i,type:"downtime",...n})},Qh=({xScale:n,timeline:e,graphHeight:o})=>{const i={graphHeight:o,timeline:e,xScale:n};return t.createElement(t.Fragment,null,t.createElement(Wh,{...i}),t.createElement(Kh,{...i}),t.createElement(Yh,{...i}))},Ra=({value:n,unit:e,base:o=1e3})=>{if(E(n))return null;const r=["B","bytes","bytespersecond","B/s","B/sec","o","octets","b/s","b"].includes(e)||Number(o)===1024?" ib":"a";return dn(n).format(`0.[00]${r}`).replace(/\s|i|B/g,"")},np=({x:n,unit:e})=>t.createElement("text",{fontFamily:Ko.fontFamily,fontSize:Ko.fontSize,x:n,y:-8},e),Jh=({lines:n,graphWidth:e,base:o,leftScale:i,rightScale:a,graphHeight:r})=>{const s=({unit:x})=>y=>E(y)?"":Ra({base:o,unit:x,value:y}),[l,p,d]=Wo(n),m=!E(d),b=!E(p)&&!m,f=Math.ceil(r/30);return t.createElement(t.Fragment,null,!m&&t.createElement(np,{unit:l,x:0}),t.createElement(gb,{numTicks:f,orientation:"left",scale:i,tickFormat:s({unit:m?"":l}),tickLabelProps:()=>({...Ko,dx:-2,dy:4,textAnchor:"end"}),tickLength:2}),b&&t.createElement(t.Fragment,null,t.createElement(yb,{left:e,numTicks:f,orientation:"right",scale:a,tickFormat:s({unit:p}),tickLength:2}),t.createElement(np,{unit:p,x:e})))},Ko={fontFamily:"Roboto, sans-serif",fontSize:10},Xh=({lines:n,graphHeight:e,graphWidth:o,leftScale:i,rightScale:a,xScale:r,xAxisTickFormat:s,base:l})=>{const{format:p}=Pn(),d=b=>p({date:new Date(b),formatString:s}),m=Math.ceil(o/82);return t.createElement(t.Fragment,null,t.createElement(ub,{numTicks:m,scale:r,tickFormat:d,tickLabelProps:()=>({...Ko,textAnchor:"middle"}),top:e}),t.createElement(Jh,{base:l,graphHeight:e,graphWidth:o,leftScale:i,lines:n,rightScale:a}))},Zh=n=>{const[e,o]=t.useState(void 0),i=({xStart:d,xEnd:m})=>{const b=Ht(Lr,d),f=Dr(Lr,m);return _b(b,f)};return{annotationHovered:e,changeAnnotationHovered:({xScale:d,mouseX:m,timeline:b})=>{const f=i({xEnd:hb(m),xStart:wb(m)});o(oe(({startDate:x,endDate:y,date:g})=>E(x)?f(d(new Date(g))):i({xEnd:d(y?new Date(y):Eo(d.domain())||n),xStart:d(new Date(x))})(m),b!=null?b:[]))},getFill:({color:d,event:m})=>ve([[E,rn(jn(d,.3))],[j(m),rn(jn(d,.5))],[zn,rn(jn(d,.1))]])(e),getIconColor:({color:d,event:m})=>ve([[E,rn(d)],[W(j(m),ln),rn(jn(d,.2))],[zn,rn(d)]])(e),getStrokeOpacity:d=>ve([[E,rn(.5)],[j(d),rn(.7)],[zn,rn(.2)]])(e),getStrokeWidth:d=>ve([[E,rn(1)],[j(d),rn(3)],[zn,rn(1)]])(e),setAnnotationHovered:o}},Ga=50,nw=P({translationZone:{cursor:"pointer"}}),ep=({direction:n,onDirectionHover:e,directionHovered:o})=>{const i=qn(),a=nw(),{graphHeight:r,graphWidth:s,marginLeft:l,marginTop:p,shiftTime:d}=Da();return re({Component:t.createElement(Ci,{className:a.translationZone,fill:j(o,n)?jn(i.palette.common.white,.5):"transparent",height:r,width:Ga,x:(j(n,Yo.backward)?$r(Ga):s)+l,y:p,onClick:()=>d==null?void 0:d(n),onMouseLeave:e(null),onMouseOver:e(n)}),memoProps:[o,r,s,l,p]})},wt=20,ew=P({icon:{cursor:"pointer"}}),tp=({xIcon:n,Icon:e,direction:o,directionHovered:i,ariaLabel:a})=>{const r=ew(),{t:s}=D(),{graphHeight:l,marginTop:p,shiftTime:d,loading:m}=Da(),b=()=>m||ln(j(i,o))?"disabled":"primary",f={"aria-label":s(a),className:r.icon,height:wt,onClick:()=>ln(m)&&(d==null?void 0:d(o)),width:wt,x:n,y:l/2-wt/2+p};return re({Component:t.createElement("g",null,t.createElement("svg",{...f},t.createElement("rect",{fill:"transparent",height:wt,width:wt}),t.createElement(e,{color:b()}))),memoProps:[n,o,a,m,i,l]})};var Yo;(function(n){n[n.backward=0]="backward",n[n.forward=1]="forward"})(Yo||(Yo={}));const op=t.createContext(void 0),Da=()=>t.useContext(op),tw=()=>{const[n,e]=t.useState(null),{graphWidth:o,canAdjustTimePeriod:i}=Da(),a=r=>()=>e(r);return ln(i)?null:t.createElement(t.Fragment,null,t.createElement(tp,{Icon:kb,ariaLabel:Fu,direction:0,directionHovered:n,xIcon:0}),t.createElement(tp,{Icon:Eb,ariaLabel:Hu,direction:1,directionHovered:n,xIcon:o+Ga+wt}),t.createElement(ep,{direction:0,directionHovered:n,onDirectionHover:a}),t.createElement(ep,{direction:1,directionHovered:n,onDirectionHover:a}))};var Ha;(function(n){n[n.dot=0]="dot",n[n.bar=1]="bar"})(Ha||(Ha={}));const ow=P(n=>({disabled:{color:n.palette.text.disabled},icon:{backgroundColor:({color:e})=>e,borderRadius:({variant:e})=>j(0,e)?"50%":0,height:({variant:e})=>j(0,e)?9:"100%",marginRight:n.spacing(1),width:9}})),ip=({disabled:n,color:e,variant:o=1})=>{const i=ow({color:e,variant:o});return t.createElement("div",{className:$t(i.icon,{[i.disabled]:n})})},iw=P(n=>({emphasized:{fontWeight:"bold"},metric:{alignItems:"center",display:"grid",gridAutoFlow:"column",gridGap:n.spacing(.5),gridTemplateColumns:"auto 1fr auto",justifyContent:"flex-start"},tooltip:{display:"flex",flexDirection:"column"},value:{justifySelf:"flex-end"}})),aw=n=>{const e=50;return n.length<e?n:`${Ab(e/2,n)}...${vb(e/2,n)}`},rw=()=>{const n=iw(),{metricsValue:e,getFormattedMetricData:o,formatDate:i}=Fa();return t.createElement("div",{className:n.tooltip},t.createElement(V,{align:"center",className:n.emphasized,variant:"caption"},i()),e==null?void 0:e.metrics.map(a=>{const r=o(a);return t.createElement("div",{className:n.metric,key:a},t.createElement(ip,{color:(r==null?void 0:r.color)||"",variant:Ha.dot}),t.createElement(V,{noWrap:!0,variant:"caption"},aw((r==null?void 0:r.name)||"")),t.createElement(V,{className:$t([n.value,n.emphasized]),variant:"caption"},r==null?void 0:r.formattedValue))}))},cw=()=>{const[n,e]=t.useState(null),{format:o}=Pn(),{tooltipData:i,tooltipLeft:a,tooltipTop:r,tooltipOpen:s,showTooltip:l,hideTooltip:p}=Mr();return{changeMetricsValue:({newMetricsValue:f,displayTooltipValues:x})=>{if(e(f),Rt(ln(x),E(f))){p();return}l({tooltipData:kn(f==null?void 0:f.metrics)?void 0:t.createElement(rw,null),tooltipLeft:(f==null?void 0:f.x)||0,tooltipTop:(f==null?void 0:f.y)||0})},formatDate:()=>o({date:new Date((n==null?void 0:n.timeValue.timeTick)||0),formatString:pt}),getFormattedMetricData:f=>{if(E(n))return null;const x=n==null?void 0:n.timeValue[f],{color:y,name:g,unit:u}=Ol({lines:n.lines,metric:f}),A=Ra({base:n.base,unit:u,value:x});return{color:y,formattedValue:A,name:g,unit:u}},hideTooltip:p,metricsValue:n,tooltipData:i,tooltipLeft:a,tooltipOpen:s,tooltipTop:r}},ap=t.createContext(void 0),Fa=()=>t.useContext(ap),_t=(n,e)=>j(n,e),sw=t.memo(Xh,_t),rp=t.memo(Ci,_t),lw=t.memo(zb,_t),pw=t.memo(Sb,_t),dw=t.memo(vh,_t),mw=t.memo(Qh,_t),Xn={bottom:30,left:45,right:45,top:30},Pa=165,bw=P(n=>({addCommentButton:{fontSize:10},addCommentTooltip:{display:"grid",fontSize:10,gridAutoFlow:"row",justifyItems:"center",padding:n.spacing(.5),position:"absolute"},container:{position:"relative"},graphLoader:{alignItems:"center",backgroundColor:jn(n.palette.common.white,.5),display:"flex",height:"100%",justifyContent:"center",position:"absolute",width:"100%"},overlay:{cursor:({onAddComment:e})=>E(e)?"normal":"crosshair"},tooltip:{padding:12,zIndex:n.zIndex.tooltip}})),cp=({values:n,height:e,stackedValues:o})=>{const i=vi(Ye(n),Ye(o)),a=_o(Qe(n),Qe(o));return Ai({domain:[i,a],nice:!0,range:[e,i===a&&a===0?e:0]})},xw=({width:n,height:e,timeSeries:o,base:i,lines:a,xAxisTickFormat:r,timeline:s,tooltipPosition:l,resource:p,addCommentTooltipLeft:d,addCommentTooltipTop:m,addCommentTooltipOpen:b,onTooltipDisplay:f,onAddComment:x,hideAddCommentTooltip:y,showAddCommentTooltip:g,format:u,applyZoom:A,shiftTime:w,loading:k,canAdjustTimePeriod:T,displayEventAnnotations:I,displayTooltipValues:C})=>{const{t:N}=D(),H=bw({onAddComment:x}),[G,R]=t.useState(!1),[O,B]=t.useState(),[U,Q]=t.useState(null),[$,sn]=t.useState(null),{canComment:yn}=ht(),Cn=qn(),[Vn,Tn]=t.useState(!1),{containerRef:Y,containerBounds:In,TooltipInPortal:Zn}=Cb({detectBounds:!0,scroll:!0}),En=t.useContext(Yl),{changeMetricsValue:J,metricsValue:F,hideTooltip:on,tooltipData:bn,tooltipLeft:Nn,tooltipTop:co,tooltipOpen:ai}=Fa(),ne=n>0?n-Xn.left-Xn.right:0,Ln=e>0?e-Xn.top-Xn.bottom:0,At=Zh(ne),vt=un=>{un.key==="Escape"&&y()};t.useEffect(()=>(document.addEventListener("keydown",vt,!1),()=>{document.removeEventListener("keydown",vt,!1)}),[]);const An=t.useMemo(()=>Tb({domain:[Ye(o.map(qo)),Qe(o.map(qo))],range:[0,ne]}),[ne,o]),[so,lo,Ya]=Wo(a),ri=t.useMemo(()=>{const un=E(Ya)?Ca({lines:a,timeSeries:o,unit:so}):_h({lines:a,timeSeries:o}),hn=(E(Ya)&&ln(E(so))?Ul({lines:a,unit:so}):!1)?Bl({lines:ut(a),timeSeries:o}):[0];return cp({height:Ln,stackedValues:hn,values:un})},[o,a,so,Ln]),ci=t.useMemo(()=>{const un=Ca({lines:a,timeSeries:o,unit:lo}),hn=(E(lo)?!1:Ul({lines:a,unit:lo}))?Bl({lines:ut(a),timeSeries:o}):[0];return cp({height:Ln,stackedValues:hn,values:un})},[o,a,lo,Ln]),jp=Ib(Nb).left,Qa=un=>{const vn=An.invert(un-Xn.left),hn=jp(wh(o),vn,1);return o[hn]},Ja=({x:un,y:vn})=>{const hn=Qa(un),zt=hh(hn).filter(po=>{const nd=Ol({lines:a,metric:po});return!E(hn[po])&&!E(nd)});J({displayTooltipValues:C,newMetricsValue:{base:i,lines:a,metrics:zt,timeValue:hn,x:un,y:vn}})},qp=t.useCallback(un=>{Tn(!0);const{x:vn,y:hn}=Ti(un)||{x:0,y:0},pe=vn-Xn.left;if(At.changeAnnotationHovered({mouseX:pe,timeline:s,xScale:An}),U){sn({end:Ht(pe,U)?pe:U,start:Gr(pe,U)?pe:U}),J({displayTooltipValues:C,newMetricsValue:null}),on();return}Ja({x:vn,y:hn}),f==null||f([vn,hn])},[In,a,$,s,C]);t.useEffect(()=>{const{top:un,bottom:vn}=En,hn=In.top>un&&In.bottom<vn;if(Vn||!hn)return;if(E(l)){J({displayTooltipValues:C,newMetricsValue:null}),on();return}const[pe,zt]=l;Ja({x:pe,y:zt})},[l]),t.useEffect(()=>{ln(C)&&on()},[C]);const Xa=()=>{sn(null),Q(null)},Wp=()=>{J({displayTooltipValues:C,newMetricsValue:null}),on(),Tn(!1),f==null||f(),At.setAnnotationHovered(void 0),!ln(E(U))&&Xa()},Kp=un=>{if(sn(null),Q(null),!yn([p])||E(x))return;if(($==null?void 0:$.start)!==($==null?void 0:$.end)){A==null||A({end:An.invert(($==null?void 0:$.end)||ne),start:An.invert(($==null?void 0:$.start)||0)});return}const{x:vn,y:hn}=Ti(un)||{x:0,y:0},{timeTick:pe}=Qa(vn),zt=new Date(pe);B(zt);const po=n-vn<Pa;g({tooltipLeft:po?vn-Pa:vn,tooltipTop:hn})},Yp=()=>{R(!0),y()},Qp=un=>{R(!1),x==null||x(un)},Jp=un=>{if(E(x))return;const{x:vn}=Ti(un)||{x:0},hn=vn-Xn.left;Q(hn),sn({end:hn,start:hn}),y()},Xp=F&&ln(kn(F==null?void 0:F.metrics)),Za=((F==null?void 0:F.x)||Nn||0)-Xn.left,nr=((F==null?void 0:F.y)||co||0)-Xn.top,Zp=Math.abs((($==null?void 0:$.end)||0)-(($==null?void 0:$.start)||0));return t.createElement(Jl.Provider,{value:At},t.createElement(Rb,{onClickAway:y},t.createElement("div",{className:H.container},ai&&bn&&t.createElement(Zn,{className:H.tooltip,key:Math.random(),left:Nn,top:co},bn),k&&t.createElement("div",{className:H.graphLoader},t.createElement(ui,null)),t.createElement("svg",{height:e,ref:Y,width:"100%",onMouseUp:Xa},t.createElement(Gb,{left:Xn.left,top:Xn.top},t.createElement(pw,{height:Ln,scale:ci||ri,stroke:Ao[100],width:ne}),t.createElement(lw,{height:Ln,scale:An,stroke:Ao[100],width:ne}),t.createElement(sw,{base:i,graphHeight:Ln,graphWidth:ne,leftScale:ri,lines:a,rightScale:ci,xAxisTickFormat:r,xScale:An}),t.createElement(dw,{graphHeight:Ln,leftScale:ri,lines:a,rightScale:ci,timeSeries:o,xScale:An}),I&&t.createElement(mw,{graphHeight:Ln,timeline:s,xScale:An}),t.createElement(rp,{fill:jn(Cn.palette.primary.main,.2),height:Ln,stroke:jn(Cn.palette.primary.main,.5),width:Zp,x:($==null?void 0:$.start)||0,y:0}),t.createElement(rp,{className:H.overlay,fill:"transparent",height:Ln,width:ne,x:0,y:0,onMouseDown:Jp,onMouseLeave:Wp,onMouseMove:qp,onMouseUp:Kp}),(Xp||bn)&&t.createElement(t.Fragment,null,t.createElement(Si,{from:{x:Za,y:0},pointerEvents:"none",stroke:Ao[400],strokeWidth:1,to:{x:Za,y:Ln}}),t.createElement(Si,{from:{x:0,y:nr},pointerEvents:"none",stroke:Ao[400],strokeWidth:1,to:{x:ne,y:nr}}))),t.createElement(op.Provider,{value:{canAdjustTimePeriod:T,graphHeight:Ln,graphWidth:ne,loading:k,marginLeft:Xn.left,marginTop:Xn.top,shiftTime:w}},t.createElement(tw,null))),b&&t.createElement(Wn,{className:H.addCommentTooltip,style:{left:d,top:m,width:Pa}},t.createElement(V,{variant:"caption"},u({date:new Date(O),formatString:pt})),t.createElement(Ae,{className:H.addCommentButton,color:"primary",size:"small",onClick:Yp},N(xa))),G&&t.createElement(Ql,{date:O,resource:p,onClose:()=>{R(!1)},onSuccess:Qp}))))},fw=["addCommentTooltipLeft","addCommentTooltipTop","addCommentTooltipOpen","width","height","timeSeries","base","lines","xAxisTickFormat","timeline","tooltipPosition","resource","eventAnnotationsActive","loading","canAdjustTimePeriod"],gw=le({Component:xw,memoProps:fw}),yw=n=>{const{format:e}=Pn(),{tooltipLeft:o,tooltipTop:i,tooltipOpen:a,showTooltip:r,hideTooltip:s}=Mr();return t.createElement(gw,{...n,addCommentTooltipLeft:o,addCommentTooltipOpen:a,addCommentTooltipTop:i,format:e,hideAddCommentTooltip:s,showAddCommentTooltip:r})},uw=P(n=>({caption:({panelWidth:e})=>({color:jn(n.palette.common.black,.6),lineHeight:1.2,marginRight:n.spacing(1),maxWidth:.85*e,overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}),hidden:{color:n.palette.text.disabled},icon:{borderRadius:"50%",height:9,marginRight:n.spacing(1),width:9},item:{display:"grid",gridTemplateColumns:"min-content minmax(50px, 1fr)",margin:n.spacing(0,1,1,1)},items:{display:"grid",gridTemplateColumns:"repeat(auto-fit, minmax(150px, 1fr))",justifyContent:"center",maxHeight:n.spacing(15),overflowY:"auto",width:"100%"},legendData:{display:"flex",flexDirection:"column",justifyContent:"space-between"},legendValue:{fontWeight:n.typography.body1.fontWeight},minMaxAvgContainer:{columnGap:"8px",display:"grid",gridAutoRows:`${n.spacing(2)}px`,gridTemplateColumns:"repeat(2, min-content)",whiteSpace:"nowrap"},minMaxAvgValue:{fontWeight:600},toggable:{"&:hover":{color:n.palette.common.black},cursor:"pointer"}})),hw=({lines:n,onToggle:e,onSelect:o,toggable:i,onHighlight:a,onClearHighlight:r,panelWidth:s,base:l})=>{const p=uw({panelWidth:s}),d=qn(),{metricsValue:m,getFormattedMetricData:b}=Fa(),{t:f}=D(),x=({metric:g,legend:u,name:A,display:w})=>{const k=u||A,T=Sr("#",k)?Or("#")[1]:k;return t.createElement("div",{onMouseEnter:()=>a(g),onMouseLeave:()=>r()},t.createElement(we,{placement:"top",title:k},t.createElement(V,{className:$t({[p.hidden]:!w,[p.toggable]:i},p.caption),component:"p",variant:"caption",onClick:I=>{if(!!i){if(I.ctrlKey||I.metaKey){e(g);return}o(g)}}},T)))},y=({value:g,unit:u})=>Ra({base:l,unit:u,value:g?parseInt(g,10):null})||"N/A";return t.createElement("div",{className:p.items},n.map(g=>{var N;const{color:u,name:A,display:w}=g,k=w?u:jn(d.palette.text.disabled,.2),T=oe(j(g.metric),tt([],"metrics",m)),I=T&&((N=b(T))==null?void 0:N.formattedValue),C=[{label:Lu,value:g.min},{label:$u,value:g.max},{label:Mu,value:g.average}];return t.createElement("div",{className:p.item,key:A},t.createElement(ip,{color:k,disabled:!w}),t.createElement("div",{className:p.legendData},t.createElement("div",null,x(g),t.createElement(V,{className:p.caption,component:"p",variant:"caption"},`(${g.unit})`)),I?t.createElement(V,{className:p.legendValue,variant:"h6"},I):t.createElement("div",{className:p.minMaxAvgContainer},C.map(({label:H,value:G})=>t.createElement("div",{"aria-label":f(H),key:H},t.createElement(V,{variant:"caption"},f(H),": "),t.createElement(V,{className:p.minMaxAvgValue,variant:"caption"},y({unit:g.unit,value:G})))))))}))},ww=["panelWidth","lines","toggable"],_w=le({Component:hw,memoProps:ww}),kw=n=>{const{panelWidth:e}=Hn();return t.createElement(_w,{...n,panelWidth:e})},Ew=P(n=>({loadingSkeleton:{display:"grid",gridGap:n.spacing(1),gridTemplateRows:({graphHeight:e,displayTitleSkeleton:o})=>`${o?"1fr":""} ${e}px ${n.spacing(7)}px`,height:"100%"},loadingSkeletonLine:{paddingBottom:n.spacing(1),transform:"none"}})),Aw=({graphHeight:n,displayTitleSkeleton:e})=>{const o=Ew({displayTitleSkeleton:e,graphHeight:n}),i=t.createElement($n,{className:o.loadingSkeletonLine});return t.createElement("div",{className:o.loadingSkeleton},e&&i,i,i)},vw=({element:n,title:e})=>Db(n).then(o=>{const i=o.toDataURL("image/png;base64"),a=document.createElement("a"),r=new Date().toISOString().substring(0,19);a.download=`${e}-${r}.png`,a.href=i,a.click(),a.remove()}),zw=P(n=>({container:{display:"grid",flexDirection:"column",gridGap:n.spacing(1),gridTemplateRows:({graphHeight:e,displayTitle:o})=>`${o?"auto":""} ${e}px auto`,height:"100%",justifyItems:"center",width:"auto"},exportToPngButton:{justifySelf:"end"},graphHeader:{display:"grid",gridTemplateColumns:"0.1fr 1fr 0.1fr",justifyItems:"center",width:"100%"},graphTranslation:{columnGap:`${n.spacing(1)}px`,display:"grid",gridTemplateColumns:({canAdjustTimePeriod:e})=>e?"min-content auto min-content":"auto",justifyContent:({canAdjustTimePeriod:e})=>e?"space-between":"center",margin:n.spacing(0,1),width:"90%"},legend:{alignItems:"center",display:"flex",flexWrap:"wrap",justifyContent:"center",width:"100%"},loadingContainer:{height:n.spacing(2),width:n.spacing(2)},noDataContainer:{alignItems:"center",display:"flex",height:"100%",justifyContent:"center"}})),Sw=2,sp=({endpoint:n,graphHeight:e,xAxisTickFormat:o=Jt,toggableLegend:i=!1,timeline:a,tooltipPosition:r,onTooltipDisplay:s,resource:l,onAddComment:p,adjustTimePeriod:d,customTimePeriod:m,resourceDetailsUpdated:b=!0,displayEventAnnotations:f=!1,displayTooltipValues:x=!1,displayTitle:y=!0})=>{const g=zw({canAdjustTimePeriod:ln(E(d)),displayTitle:y,graphHeight:e}),{t:u}=D(),[A,w]=t.useState([]),[k,T]=t.useState(),[I,C]=t.useState(),[N,H]=t.useState(),[G,R]=t.useState(!1),O=t.useRef(),{sendRequest:B,sending:U}=fn({request:ce}),Q=cw();if(t.useEffect(()=>{E(n)||B(n).then(J=>{w(gh(J)),H(J.global.base),C(J.global.title);const F=uh(J);if(k){T(F.map(on=>{var bn,Nn;return{...on,display:(Nn=(bn=oe(pn("name",on.name),k))==null?void 0:bn.display)!=null?Nn:!0}}));return}T(F)})},[n]),E(k)||E(a)||E(n))return t.createElement(Aw,{displayTitleSkeleton:y,graphHeight:e});if(kn(A)||kn(k))return t.createElement("div",{className:g.noDataContainer},t.createElement(V,{align:"center",variant:"body1"},u(Z0)));const $=ki(an("name"),k),sn=Mn(pn("display",!1),$),yn=J=>oe(pn("metric",J),k),Cn=J=>{const F=yn(J);T([...Mn(pn("metric",J),k),{...F,display:!F.display}])},Vn=J=>{const F=K(on=>({...on,highlight:!1}),k);T([...Mn(pn("metric",J),F),{...yn(J),highlight:!0}])},Tn=()=>{T(K(J=>({...J,highlight:void 0}),k))},Y=J=>{const F=yn(J),on=W(it,j(F))(sn);if(sn.length===1&&on||kn(sn)){T(K(Nn=>({...Nn,display:!0}),k));return}T(K(Nn=>({...Nn,display:j(Nn,F)}),k))},In=({property:J,direction:F,timePeriod:on})=>{const bn=(on.end.getTime()-on.start.getTime())/Sw;return new Date(Fr(an(J,on).getTime(),j(F,Yo.backward)?$r(bn):bn))},Zn=J=>{E(m)||d==null||d({end:In({direction:J,property:yt.end,timePeriod:m}),start:In({direction:J,property:yt.start,timePeriod:m})})},En=()=>{R(!0),vw({element:O.current,title:`${l==null?void 0:l.name}-performance`}).finally(()=>{R(!1)})};return t.createElement(ap.Provider,{value:Q},t.createElement("div",{className:g.container,ref:O},y&&t.createElement("div",{className:g.graphHeader},t.createElement("div",null),t.createElement(V,{color:"textPrimary",variant:"body1"},I),t.createElement("div",{className:g.exportToPngButton},t.createElement(Yt,{alignCenter:!1,loading:G,loadingIndicatorSize:16},t.createElement(Rn,{disabled:E(a),title:u(Ru),onClick:En},t.createElement(Hb,{style:{fontSize:18}}))))),t.createElement(Dt,null,({width:J,height:F})=>t.createElement(yw,{applyZoom:d,base:N,canAdjustTimePeriod:ln(E(d)),displayEventAnnotations:f,displayTooltipValues:x,height:F,lines:sn,loading:ln(b)&&U,resource:l,shiftTime:Zn,timeSeries:A,timeline:a,tooltipPosition:r,width:J,xAxisTickFormat:o,onAddComment:p,onTooltipDisplay:s})),t.createElement("div",{className:g.legend},t.createElement(kw,{base:N,lines:$,toggable:i,onClearHighlight:Tn,onHighlight:Vn,onSelect:Y,onToggle:Cn}))))},Cw=P(n=>({graph:{height:"100%",margin:"auto",width:"100%"},graphContainer:{display:"grid",gridTemplateRows:"1fr",padding:n.spacing(2,1,1)}})),lp=({resource:n,selectedTimePeriod:e,getIntervalDates:o,periodQueryParameters:i,graphHeight:a,onTooltipDisplay:r,tooltipPosition:s,customTimePeriod:l,adjustTimePeriod:p,resourceDetailsUpdated:d})=>{var N;const m=Cw(),{alias:b}=He(),{sendRequest:f}=fn({decoder:$l,request:Fl}),[x,y]=t.useState(),g=((N=Il())==null?void 0:N.graphOptions)||Nl,u=mn([Re.displayTooltips,"value"],g),A=mn([Re.displayEvents,"value"],g),w=mn(["links","endpoints","performance_graph"],n),k=mn(["links","endpoints","timeline"],n),T=()=>{if(Rt(E(k),ln(A))){y([]);return}const[H,G]=o();f({endpoint:k,parameters:{limit:(e==null?void 0:e.timelineEventsLimit)||l.timelineLimit,search:{conditions:[{field:"date",values:{$gt:H,$lt:G}}]}}}).then(({result:R})=>{y(R)})};t.useEffect(()=>{E(w)||T()},[w,e,l,A]);const I=()=>{if(!E(w))return`${w}${i}`},C=({date:H,comment:G})=>{y([...x,{contact:{name:b},content:G,date:H,id:Math.random(),type:"comment"}])};return t.createElement(Wn,{className:m.graphContainer},t.createElement("div",{className:m.graph},t.createElement(sp,{toggableLegend:!0,adjustTimePeriod:p,customTimePeriod:l,displayEventAnnotations:A,displayTooltipValues:u,endpoint:I(),graphHeight:a,resource:n,resourceDetailsUpdated:d,timeline:x,tooltipPosition:s,xAxisTickFormat:(e==null?void 0:e.dateTimeFormat)||l.xAxisTickFormat,onAddComment:C,onTooltipDisplay:r})))},Tw=P(n=>({container:{display:"grid",gridRowGap:n.spacing(2),gridTemplateRows:"auto 1fr"},exportToPngButton:{display:"flex",justifyContent:"space-between",margin:n.spacing(0,1,1,2)},graph:{height:"100%",margin:"auto",width:"100%"},graphContainer:{display:"grid",gridTemplateRows:"1fr",padding:n.spacing(2,1,1)}})),Iw=({details:n,tabParameters:e,setGraphTabParameters:o})=>{const i=Tw(),{selectedTimePeriod:a,changeSelectedTimePeriod:r,periodQueryParameters:s,getIntervalDates:l,customTimePeriod:p,changeCustomTimePeriod:d,adjustTimePeriod:m,resourceDetailsUpdated:b}=Tl({defaultGraphOptions:mn(["graph","graphOptions"],e),defaultSelectedCustomTimePeriod:mn(["graph","selectedCustomTimePeriod"],e),defaultSelectedTimePeriodId:mn(["graph","selectedTimePeriodId"],e),details:n,onTimePeriodChange:o}),x=Rl({changeTabGraphOptions:y=>{o({...e.graph,graphOptions:y})},graphTabParameters:e.graph});return t.createElement(Sa.Provider,{value:x},t.createElement("div",{className:i.container},t.createElement(Hl,{changeCustomTimePeriod:d,customTimePeriod:p,selectedTimePeriodId:a==null?void 0:a.id,onChange:r}),t.createElement(lp,{adjustTimePeriod:m,customTimePeriod:p,getIntervalDates:l,graphHeight:280,periodQueryParameters:s,resource:n,resourceDetailsUpdated:b,selectedTimePeriod:a})))},Nw=le({Component:Iw,memoProps:["details","tabParameters"]}),Rw=({details:n})=>{const{tabParameters:e,setGraphTabParameters:o}=Hn();return t.createElement(Nw,{details:n,setGraphTabParameters:o,tabParameters:e})},Gw=P(n=>({container:{padding:n.spacing(1)}})),Dw=()=>{const{t:n}=D(),e=Gw();return t.createElement(Wn,{className:e.container},t.createElement(V,{align:"center",variant:"body1"},n(iu)))},Hw=P(n=>({container:{alignContent:"flex-start",alignItems:"center",display:"grid",gridGap:n.spacing(1),height:"100%",justifyItems:"center",width:"100%"},entities:{display:"grid",gridAutoFlow:"row",gridGap:n.spacing(1),gridTemplateColumns:"repeat(auto-fit, minmax(320px, 1fr))",width:"100%"},filter:{width:"100%"}})),Fw=({limit:n,filter:e,details:o,reloadDependencies:i=[],loadingSkeleton:a,loading:r,preventReloadWhen:s=!1,selectedResourceId:l,sendListingRequest:p,children:d})=>{const{t:m}=D(),b=Hw(),[f,x]=t.useState(),[y,g]=t.useState(1),[u,A]=t.useState(0),[w,k]=t.useState(!1),T=({atPage:G}={atPage:y})=>p({atPage:G}).then(R=>{const{meta:O}=R;return A(O.total),R}).finally(()=>{k(!1)}),I=()=>{g(1),T({atPage:1}).then(({result:G})=>{x(G)})};t.useEffect(()=>{E(o)&&x(void 0),!(y!==1||E(o)||s)&&I()},[o]),t.useEffect(()=>{E(f)||y===1||T().then(({result:G})=>{x(Fb(f,G))})},[y]),t.useEffect(()=>{E(o)||E(f)||(x(void 0),I())},i),t.useEffect(()=>{l!==(o==null?void 0:o.id)&&(x(void 0),g(1))},[l]);const C=Math.ceil(u/n),H=ac({action:()=>{k(!0),g(y+1)},loading:r,maxPage:C,page:y});return t.createElement("div",{className:b.container},t.createElement("div",{className:b.filter},e),y>1&&t.createElement(Ae,{color:"primary",size:"small",startIcon:t.createElement(Br,null),variant:"contained",onClick:I},m(sa)),t.createElement("div",{className:b.entities},ve([[rn(E(f)),rn(a)],[kn,rn(t.createElement(Dw,null))],[zn,rn(t.createElement(t.Fragment,null,d({entities:f,infiniteScrollTriggerRef:H})))]])(f)),w&&t.createElement(ui,null))},Pw=le({Component:Fw,memoProps:["selectedResourceId","limit","details","reloadDependencies","loading","preventReloadWhen","filter"]}),pp=n=>{const{selectedResourceId:e}=Hn();return t.createElement(Pw,{selectedResourceId:e,...n})},dp=P(n=>({root:{fontSize:n.typography.body2.fontSize,height:18}})),mp=n=>{const e=dp();return t.createElement(Ue,{classes:{root:e.root},...n})},Lw=P(()=>({information:({bold:n})=>({fontWeight:n?600:"unset"})})),kt=({content:n,bold:e=!1})=>{const o=Lw({bold:e});return t.createElement(V,{className:o.information,variant:"body2"},Ii(Ni.sanitize(Ta(n))))},$w=[{id:"event",name:pa},{id:"notification",name:ma},{id:"comment",name:ye},{id:"acknowledgement",name:da},{id:"downtime",name:Oo}],ro=P(n=>({event:{alignItems:"center",display:"grid",gridAutoFlow:"columns",gridGap:n.spacing(2),gridTemplateColumns:"auto 1fr auto",padding:n.spacing(1)},info:{display:"grid",gridAutoFlow:"row",gridGap:n.spacing(1)},infoHeader:{alignItems:"start",display:"grid",gridAutoFlow:"column",gridGap:n.spacing(2),gridTemplateColumns:"minmax(80px, auto) auto 1fr"},title:{alignItems:"center",display:"grid",gridAutoColumns:"auto",gridAutoFlow:"column",gridGap:n.spacing(2),justifyContent:"flex-start"}})),Qo=({event:n})=>{const{toTime:e}=Pn();return t.createElement(V,{variant:"caption"},e(n.date))},Jo=({event:n})=>{var i;const e=dp(),o=((i=n.contact)==null?void 0:i.name)||"";return t.createElement(Cr,{className:e.root,icon:t.createElement($b,null),label:o,size:"small",variant:"outlined"})},Mw=({event:n})=>{var i,a;const{t:e}=D(),o=ro();return t.createElement("div",{className:o.event},t.createElement(Pb,{"aria-label":e(pa),color:"primary"}),t.createElement("div",{className:o.info},t.createElement("div",{className:o.infoHeader},t.createElement(Qo,{event:n}),t.createElement(mp,{label:e((i=n.status)==null?void 0:i.name),severityCode:(a=n.status)==null?void 0:a.severity_code}),t.createElement(V,{style:{justifySelf:"end"},variant:"caption"},`${e(Hs)}: ${n.tries}`)),t.createElement(kt,{bold:!0,content:n.content})))},Ow=({event:n})=>{const{t:e}=D(),o=ro();return t.createElement("div",{className:o.event},t.createElement(Pr,{"aria-label":e(ye),color:"primary"}),t.createElement("div",{className:o.info},t.createElement("div",{className:o.infoHeader},t.createElement(Qo,{event:n}),t.createElement("div",{className:o.title},t.createElement(Jo,{event:n}))),t.createElement(kt,{bold:!0,content:n.content})))},Bw=({event:n})=>{const{t:e}=D(),o=ro();return t.createElement("div",{className:o.event},t.createElement(Ea,{"aria-label":e(da)}),t.createElement("div",{className:o.info},t.createElement("div",{className:o.infoHeader},t.createElement(Qo,{event:n}),t.createElement("div",{className:o.title},t.createElement(Jo,{event:n}))),t.createElement(kt,{bold:!0,content:n.content})))},Uw=({event:n})=>{const{t:e}=D(),{toDateTime:o}=Pn(),i=ro(),a=()=>{const r=o(n.startDate),s=`${e(mt)} ${r}`;if(E(n.endDate))return s;const l=o(n.endDate);return`${s} ${e(bt)} ${l}`};return t.createElement("div",{className:i.event},t.createElement(ka,{"aria-label":e(Oo)}),t.createElement("div",{className:i.info},t.createElement("div",{className:i.infoHeader},t.createElement(V,{variant:"caption"},a()),t.createElement("div",{className:i.title},t.createElement(Jo,{event:n}))),t.createElement(kt,{bold:!0,content:n.content})))},Vw=({event:n})=>{const{t:e}=D(),o=ro();return t.createElement("div",{className:o.event},t.createElement(Lb,{"aria-label":e(ma),color:"primary"}),t.createElement("div",{className:o.info},t.createElement("div",{className:o.infoHeader},t.createElement(Qo,{event:n}),t.createElement("div",{className:o.title},t.createElement(Jo,{event:n}))),t.createElement(kt,{bold:!0,content:n.content})))},jw={acknowledgement:Bw,comment:Ow,downtime:Uw,event:Mw,notification:Vw},qw=P(n=>({events:{display:"grid",gridAutoFlow:"row",gridGap:n.spacing(1),width:"100%"}})),Ww=({timeline:n,infiniteScrollTriggerRef:e})=>{const o=qw(),{toDate:i}=Pn(),a=W(Bb((s,l)=>s.concat(l),[],W(an("date"),i)),hi,Mb([Ob(W(it,Date.parse))]))(n),r=a.map(it);return t.createElement("div",null,a.map(([s,l])=>{const p=j(Eo(r),s);return t.createElement("div",{key:s},t.createElement("div",{className:o.events},t.createElement(V,{variant:"h6"},s),l.map(d=>{const{id:m,type:b}=d,f=jw[b];return t.createElement(Wn,{key:`${m}-${b}`},t.createElement(f,{event:d}))})),p&&t.createElement("div",{ref:e}))}))},Kw=P(n=>({skeleton:{display:"grid",gridGap:n.spacing(1)}})),Yw=()=>{const n=Kw();return t.createElement("div",{className:n.skeleton},t.createElement($n,{height:20,style:{transform:"none"},width:125}),t.createElement($n,{height:100,style:{transform:"none"}}),t.createElement($n,{height:100,style:{transform:"none"}}))},Qw=P(n=>({filter:{padding:n.spacing(2)}})),Jw=({details:n})=>{const e=Qw(),{t:o}=D(),i=$w.map(x=>({...x,name:o(x.name)})),[a,r]=t.useState(i),s=30,{sendRequest:l,sending:p}=fn({decoder:$l,request:Fl}),d=()=>{if(!kn(a))return{lists:[{field:"type",values:a.map(an("id"))}]}},m=mn(["links","endpoints","timeline"],n),b=({atPage:x})=>l({endpoint:m,parameters:{limit:s,page:x,search:d()}}),f=(x,y)=>{r(y)};return t.createElement(pp,{details:n,filter:t.createElement(Wn,{className:e.filter},t.createElement(Pi,{fullWidth:!0,label:o(pa),limitTags:3,options:i,value:a,onChange:f})),limit:s,loading:p,loadingSkeleton:t.createElement(Yw,null),reloadDependencies:[a],sendListingRequest:b},({infiniteScrollTriggerRef:x,entities:y})=>t.createElement(Ww,{infiniteScrollTriggerRef:x,timeline:y}))},Xw=P(n=>({gridWithSpacing:{display:"grid",gridGap:n.spacing(1),padding:n.spacing(1)},shortcutRow:{alignItems:"center",gridAutoFlow:"column",gridGap:n.spacing(2),gridTemplateColumns:"auto auto",justifyContent:"flex-start"}})),Zw=({uris:n})=>{const{t:e}=D(),o=Xw(),i=[{Icon:yi,name:yu,uri:an("configuration",n)},{Icon:Ub,name:uu,uri:an("logs",n)},{Icon:Vb,name:hu,uri:an("reporting",n)}],a=Pt(W(an("uri"),E,ln),i);return t.createElement(Wn,{className:$t([o.gridWithSpacing])},a.map(({Icon:r,uri:s,name:l})=>t.createElement("div",{className:$t([o.gridWithSpacing,o.shortcutRow]),key:l},t.createElement(r,{color:"primary"}),t.createElement(yo,{color:"inherit",href:s,variant:"body1"},e(l)))))},bp=({title:n,uris:e})=>t.createElement(t.Fragment,null,t.createElement(V,{variant:"h6"},n),t.createElement(Zw,{uris:e})),xp=P(n=>({container:{display:"grid",gridGap:n.spacing(1)},loadingSkeleton:{display:"flex",flexDirection:"column",height:120,justifyContent:"space-between",padding:n.spacing(2)}})),n2=()=>{const n=xp();return t.createElement(Wn,{className:n.loadingSkeleton},t.createElement($n,{width:175}),t.createElement($n,{width:175}),t.createElement($n,{width:170}))},e2=({details:n})=>{const e=xp(),{t:o}=D(),i=mn(["links","uris"],n),a=mn(["parent","links","uris"],n),r=a&&_l(a),s={host:"Host",metaservice:"Meta service",service:"Service"};if(E(n))return t.createElement(n2,null);const l=o(s[n.type]);return t.createElement("div",{className:e.container},t.createElement(bp,{title:l,uris:i}),r&&t.createElement(bp,{title:o(zs),uris:a}))},t2=n=>lt({baseEndpoint:Ee,customQueryParameters:[{name:"states",value:n.states},{name:"types",value:n.resourceTypes},{name:"statuses",value:n.statuses},{name:"hostgroup_ids",value:n.hostGroupIds},{name:"servicegroup_ids",value:n.serviceGroupIds},{name:"monitoring_server_ids",value:n.monitoringServerIds},{name:"only_with_performance_data",value:n.onlyWithPerformanceData}],parameters:n}),fp=n=>e=>ce(n)(t2(e)),o2=t.memo(lp,(n,e)=>{const o=n.resource,i=e.resource,a=n.periodQueryParameters,r=e.periodQueryParameters,s=n.tooltipPosition,l=e.tooltipPosition,p=n.selectedTimePeriod,d=e.selectedTimePeriod;return j(o==null?void 0:o.id,i==null?void 0:i.id)&&j(a,r)&&j(s,l)&&j(p,d)}),i2=({services:n,infiniteScrollTriggerRef:e,periodQueryParameters:o,getIntervalDates:i,selectedTimePeriod:a,customTimePeriod:r,adjustTimePeriod:s,resourceDetailsUpdated:l})=>{const[p,d]=t.useState(),m=n.filter(W(mn(["links","endpoints","performance_graph"]),E,ln));return t.createElement(t.Fragment,null,m.map(b=>{const{id:f}=b,x=j(Eo(m),b);return t.createElement("div",{key:f},t.createElement(o2,{adjustTimePeriod:s,customTimePeriod:r,getIntervalDates:i,graphHeight:120,periodQueryParameters:o,resource:b,resourceDetailsUpdated:l,selectedTimePeriod:a,tooltipPosition:p,onTooltipDisplay:d}),x&&t.createElement("div",{ref:e}))}))},a2=P(n=>({description:{display:"grid",gridAutoFlow:"row",gridGap:n.spacing(1)},serviceCard:{padding:n.spacing(1)},serviceDetails:{alignItems:"center",display:"grid",gridAutoFlow:"columns",gridGap:n.spacing(2),gridTemplateColumns:"auto 1fr auto"}})),r2=({name:n,status:e,information:o,subInformation:i,onSelect:a})=>{const r=a2(),{t:s}=D();return t.createElement(Wn,{className:r.serviceCard},t.createElement("div",{className:r.serviceDetails},t.createElement("div",null,t.createElement(mp,{label:s(e.name),severityCode:e.severity_code})),t.createElement("div",{className:r.description},t.createElement(Kl,{name:n,onSelect:a}),t.createElement(kt,{content:o})),i&&t.createElement(V,{variant:"caption"},i)))},c2=({services:n,onSelectService:e,infiniteScrollTriggerRef:o})=>t.createElement(t.Fragment,null,n.map(i=>{const a=j(Eo(n),i),{id:r,name:s,status:l,information:p,duration:d}=i;return t.createElement("div",{key:r},t.createElement(r2,{information:p,name:s,status:l,subInformation:d,onSelect:()=>e(i)}),a&&t.createElement("div",{ref:o}))})),s2=P(n=>({skeleton:{height:62,transform:"none",width:"100%"},skeletons:{display:"grid",gridGap:n.spacing(1)}})),l2=()=>{const n=s2(),e=t.createElement($n,{className:n.skeleton});return t.createElement("div",{className:n.skeletons},e,e,e)},p2=({details:n,tabParameters:e,selectResource:o,setServicesTabParameters:i})=>{var O,B;const{t:a}=D(),[r,s]=t.useState(((O=e.services)==null?void 0:O.graphMode)||!1),[l,p]=t.useState(!1),{selectedTimePeriod:d,changeSelectedTimePeriod:m,periodQueryParameters:b,getIntervalDates:f,customTimePeriod:x,changeCustomTimePeriod:y,adjustTimePeriod:g,resourceDetailsUpdated:u}=Tl({defaultGraphOptions:mn(["services","graphTimePeriod","graphOptions"],e),defaultSelectedCustomTimePeriod:mn(["services","graphTimePeriod","selectedCustomTimePeriod"],e),defaultSelectedTimePeriodId:mn(["services","graphTimePeriod","selectedTimePeriodId"],e),details:n,onTimePeriodChange:U=>{i({graphMode:r,graphTimePeriod:U})}}),{sendRequest:A,sending:w}=fn({request:fp}),k=r?6:30,T=({atPage:U})=>A({limit:k,onlyWithPerformanceData:r?!0:void 0,page:U,resourceTypes:["service"],search:{conditions:[{field:"h.name",values:{$eq:n==null?void 0:n.name}}]}}),I=()=>{p(!1);const U=!r;s(U),i({graphMode:U,graphTimePeriod:Ri({},["services","graphTimePeriod"],e)})},N=Rl({changeTabGraphOptions:U=>{var Q,$;i({graphMode:((Q=e.services)==null?void 0:Q.graphMode)||!1,graphTimePeriod:{...($=e.services)==null?void 0:$.graphTimePeriod,graphOptions:U}})},graphTabParameters:(B=e.services)==null?void 0:B.graphTimePeriod});t.useEffect(()=>{p(!0)},[r]);const H=r?Cu:Su,G=r?t.createElement(jb,null):t.createElement(Ur,null),R=E(n)||w;return t.createElement(t.Fragment,null,t.createElement(Rn,{ariaLabel:a(H),disabled:R,title:a(H),onClick:I},G),t.createElement(Sa.Provider,{value:N},t.createElement(pp,{details:n,filter:r?t.createElement(Hl,{changeCustomTimePeriod:y,customTimePeriod:x,disabled:R,selectedTimePeriodId:d==null?void 0:d.id,onChange:m}):void 0,limit:k,loading:w,loadingSkeleton:t.createElement(l2,null),preventReloadWhen:(n==null?void 0:n.type)!=="host",reloadDependencies:[r],sendListingRequest:T},({infiniteScrollTriggerRef:U,entities:Q})=>r&&l?t.createElement(i2,{adjustTimePeriod:g,customTimePeriod:x,getIntervalDates:f,infiniteScrollTriggerRef:U,periodQueryParameters:b,resourceDetailsUpdated:u,selectedTimePeriod:d,services:Q}):t.createElement(c2,{infiniteScrollTriggerRef:U,services:Q,onSelectService:o}))))},d2=le({Component:p2,memoProps:["details","tabParameters"]}),m2=({details:n})=>{const{selectResource:e,tabParameters:o,setServicesTabParameters:i}=Hn();return t.createElement(d2,{details:n,selectResource:e,setServicesTabParameters:i,tabParameters:o})},Et=0,gp=1,yp=2,La=3,b2=4,up=5,Xo=[{Component:eh,getIsActive:()=>!0,id:Et,title:j0},{Component:m2,getIsActive:n=>n.type==="host",id:gp,title:Uy},{Component:Jw,getIsActive:()=>!0,id:yp,title:xu},{Component:Rw,getIsActive:n=>E(n)?!1:!E(mn(["links","endpoints","performance_graph"],n)),id:La,title:oo},{Component:e2,getIsActive:n=>{var a;if(E(n))return!1;const{links:e,parent:o}=n,i=(a=o==null?void 0:o.links)==null?void 0:a.uris;return wo(_l,[i,e.uris])},id:up,title:gu}],x2=P(n=>({container:{padding:n.spacing(2)}})),f2=({id:n,details:e})=>{const o=x2(),{Component:i}=oe(pn("id",n),Xo);return t.createElement("div",{className:o.container},t.createElement(i,{details:e}))},hp={details:Et,graph:La,metrics:b2,services:gp,shortcuts:up,timeline:yp},g2=n=>{const e=hp[n];return E(e)?Et:e},y2=n=>qb(hp)[n],wp={InputProps:{disableUnderline:!0},TextFieldComponent:ie,disableToolbar:!0,inputVariant:"filled",margin:"none",variant:"inline"},_p={...wp,format:"L"},kp={...wp,ampm:!1,format:"LT"},u2=({resources:n,canConfirm:e,onCancel:o,onConfirm:i,errors:a,values:r,submitting:s,handleChange:l,setFieldValue:p})=>{var w;const{t:d}=D(),{locale:m}=He(),{getDowntimeDeniedTypeAlert:b,canDowntimeServices:f}=ht(),x=Gl(),y=n.length>0,g=n.find(k=>k.type==="host"),u=k=>T=>{p(k,T)},A=b(n);return t.createElement($e,{confirmDisabled:!e,labelCancel:d(dt),labelConfirm:d(ia),labelTitle:d(Oo),open:y,submitting:s,onCancel:o,onClose:o,onConfirm:i},A&&t.createElement(Gi,{severity:"warning"},A),t.createElement(Rr,{locale:m.substring(0,2),utils:x},t.createElement(L,{container:!0,direction:"column",spacing:1},t.createElement(L,{item:!0},t.createElement(Ft,null,d(mt)),t.createElement(L,{container:!0,direction:"row",spacing:1},t.createElement(L,{item:!0,style:{width:240}},t.createElement(Vr,{KeyboardButtonProps:{"aria-label":d(ty)},"aria-label":d(As),error:(a==null?void 0:a.dateStart)!==void 0,helperText:a==null?void 0:a.dateStart,inputMode:"text",value:r.dateStart,onChange:u("dateStart"),..._p})),t.createElement(L,{item:!0,style:{width:200}},t.createElement(jr,{KeyboardButtonProps:{"aria-label":d(oy)},"aria-label":d(Ps),error:(a==null?void 0:a.timeStart)!==void 0,helperText:a==null?void 0:a.timeStart,value:r.timeStart,onChange:u("timeStart"),...kp})))),t.createElement(L,{item:!0},t.createElement(Ft,null,d(bt)),t.createElement(L,{container:!0,direction:"row",spacing:1},t.createElement(L,{item:!0,style:{width:240}},t.createElement(Vr,{KeyboardButtonProps:{"aria-label":d(iy)},"aria-label":d(vs),error:(a==null?void 0:a.dateEnd)!==void 0,helperText:a==null?void 0:a.dateEnd,value:r.dateEnd,onChange:u("dateEnd"),..._p})),t.createElement(L,{item:!0,style:{width:200}},t.createElement(jr,{KeyboardButtonProps:{"aria-label":d(ay)},"aria-label":d(Es),error:(a==null?void 0:a.timeEnd)!==void 0,helperText:a==null?void 0:a.timeEnd,value:r.timeEnd,onChange:u("timeEnd"),...kp})))),t.createElement(L,{item:!0},t.createElement(ot,{control:t.createElement(Mt,{checked:r.fixed,color:"primary",inputProps:{"aria-label":d(oa)},size:"small",onChange:l("fixed")}),label:d(oa)})),t.createElement(L,{item:!0},t.createElement(Ft,null,d(ks)),t.createElement(L,{container:!0,direction:"row",spacing:1},t.createElement(L,{item:!0,style:{width:150}},t.createElement(ie,{disabled:r.fixed,error:(w=a==null?void 0:a.duration)==null?void 0:w.value,type:"number",value:r.duration.value,onChange:l("duration.value")})),t.createElement(L,{item:!0,style:{width:150}},t.createElement($i,{disabled:r.fixed,options:[{id:"seconds",name:d(W0)},{id:"minutes",name:d(K0)},{id:"hours",name:d(Y0)}],selectedOptionId:r.duration.unit,onChange:l("duration.unit")})))),t.createElement(L,{item:!0},t.createElement(ie,{fullWidth:!0,multiline:!0,error:a==null?void 0:a.comment,label:d(ye),rows:3,value:r.comment,onChange:l("comment")})),g&&t.createElement(L,{item:!0},t.createElement(ot,{control:t.createElement(Mt,{checked:f()&&r.downtimeAttachedResources,color:"primary",disabled:!f(),inputProps:{"aria-label":Cs},size:"small",onChange:l("downtimeAttachedResources")}),label:d(Cs)})))))},Ep=n=>{const e=new Date(n.timeStart),o=new Date(n.dateStart);o.setHours(e.getHours()),o.setMinutes(e.getMinutes()),o.setSeconds(0);const i=new Date(n.timeEnd),a=new Date(n.dateEnd);return a.setHours(i.getHours()),a.setMinutes(i.getMinutes()),a.setSeconds(0),[o,a]},h2=n=>te().shape({comment:be().required(n(ue)),dateEnd:be().required(n(ue)).nullable(),dateStart:be().required(n(ue)).nullable(),duration:te().when("fixed",(e,o)=>e?o:o.shape({unit:be().required(n(ue)),value:be().required(n(ue))})),fixed:qr(),timeEnd:be().required(n(ue)).nullable(),timeStart:be().required(n(ue)).nullable()}),w2=(n,e)=>{const o={};if(n.dateStart&&n.timeStart&&n.dateEnd&&n.timeEnd){const[i,a]=Ep(n);i>=a&&(o.dateEnd=e(Q0))}return o},_2=({resources:n,onClose:e,onSuccess:o})=>{const{t:i}=D(),{showMessage:a}=ae(),r=g=>a({message:g,severity:Sn.success}),{alias:s,downtime:l}=He(),{toIsoString:p}=Pn(),{sendRequest:d,sending:m}=fn({request:$h}),b=new Date,f=l.default_duration*1e3,x=new Date(b.getTime()+f),y=uo({initialValues:{comment:void 0,dateEnd:x,dateStart:b,downtimeAttachedResources:!0,duration:{unit:"seconds",value:l.default_duration},fixed:!0,timeEnd:x,timeStart:b},onSubmit:(g,{setSubmitting:u})=>{u(!0);const[A,w]=Ep(g),k={hours:3600,minutes:60,seconds:1},T=(k==null?void 0:k[g.duration.unit])||1,I=g.duration.value*T;d({params:{...g,duration:I,endTime:p(w),startTime:p(A)},resources:n}).then(()=>{r(i(Yy)),o()})},validate:g=>w2(g,i),validationSchema:h2(i)});return t.useEffect(()=>{y.setFieldValue("comment",`${i(xy)} ${s}`)},[]),t.createElement(u2,{canConfirm:y.isValid,errors:y.errors,handleChange:y.handleChange,resources:n,setFieldValue:y.setFieldValue,submitting:m,values:y.values,onCancel:e,onConfirm:y.submitForm})},k2=({resources:n,canConfirm:e,onCancel:o,onConfirm:i,errors:a,values:r,submitting:s,handleChange:l})=>{const{t:p}=D(),{getAcknowledgementDeniedTypeAlert:d,canAcknowledgeServices:m}=ht(),b=d(n),f=n.length>0,x=n.find(y=>y.type==="host");return t.createElement($e,{confirmDisabled:!e,labelCancel:p(dt),labelConfirm:p(to),labelTitle:p(to),open:f,submitting:s,onCancel:o,onClose:o,onConfirm:i},t.createElement(L,{container:!0,direction:"column",spacing:1},b&&t.createElement(L,{item:!0},t.createElement(Gi,{severity:"warning"},b)),t.createElement(L,{item:!0},t.createElement(ie,{fullWidth:!0,multiline:!0,error:a==null?void 0:a.comment,label:p(ye),rows:3,value:r.comment,onChange:l("comment")})),t.createElement(L,{item:!0},t.createElement(ot,{control:t.createElement(Mt,{checked:r.notify,color:"primary",inputProps:{"aria-label":p(Ns)},size:"small",onChange:l("notify")}),label:p(Ns)}),t.createElement(Ft,null,p(wy))),x&&t.createElement(L,{item:!0},t.createElement(ot,{control:t.createElement(Mt,{checked:m()&&r.acknowledgeAttachedResources,color:"primary",disabled:!m(),inputProps:{"aria-label":p(us)},size:"small",onChange:l("acknowledgeAttachedResources")}),label:p(us)}))))},E2=te().shape({comment:be().required(ue),notify:qr()}),A2=({resources:n,onClose:e,onSuccess:o})=>{const{t:i}=D(),{showMessage:a}=ae(),{alias:r}=He(),{sendRequest:s,sending:l}=fn({request:Lh}),p=m=>a({message:m,severity:Sn.success}),d=uo({initialValues:{acknowledgeAttachedResources:!1,comment:void 0,notify:!1},onSubmit:m=>{s({params:m,resources:n}).then(()=>{p(i(Ky)),o()})},validationSchema:E2});return t.useEffect(()=>{d.setFieldValue("comment",`${i(ys)} ${r}`)},[]),t.createElement(k2,{canConfirm:d.isValid,errors:d.errors,handleChange:d.handleChange,resources:n,submitting:l,values:d.values,onCancel:e,onConfirm:d.submitForm})},v2=`${Ee}/acknowledgements`,z2=n=>({resources:e,disacknowledgeAttachedResources:o})=>he.delete(v2,{cancelToken:n,data:{disacknowledgement:{with_services:o},resources:K(Yn(["type","id","parent"]),e)}}),S2=({resources:n,onClose:e,onSuccess:o})=>{const{t:i}=D(),{showMessage:a}=ae(),[r,s]=t.useState(!0),{sendRequest:l,sending:p}=fn({request:z2}),{getDisacknowledgementDeniedTypeAlert:d,canDisacknowledgeServices:m}=ht(),b=d(n);t.useEffect(()=>{m()||s(!1)},[]);const f=u=>a({message:u,severity:Sn.success}),x=()=>{l({disacknowledgeAttachedResources:r,resources:n}).then(()=>{f(i(ku)),o()})},y=u=>{s(Boolean(u.target.checked))},g=n.find(pn("type","host"));return t.createElement($e,{open:!0,confirmDisabled:p,labelCancel:i(dt),labelConfirm:i(ba),labelTitle:i(ba),submitting:p,onCancel:e,onClose:e,onConfirm:x},t.createElement(L,{container:!0,direction:"column",spacing:1},b&&t.createElement(L,{item:!0},t.createElement(Gi,{severity:"warning"},b)),g&&t.createElement(L,{item:!0},t.createElement(ot,{control:t.createElement(Mt,{checked:m()&&r,color:"primary",disabled:!m(),inputProps:{"aria-label":i(Ks)},size:"small",onChange:y}),label:i(Ks)}))))},C2=`${Ee}/submit`,T2=n=>({resource:e,statusId:o,output:i,performanceData:a})=>he.post(C2,{resources:[{...Yn(["type","id","parent"],e),output:i,performance_data:a,status:o}]},{cancelToken:n}),I2=({resource:n,onClose:e,onSuccess:o})=>{const{t:i}=D(),{showMessage:a}=ae(),[r,s]=t.useState(0),[l,p]=t.useState(""),[d,m]=t.useState(""),b=[{id:0,name:i(Rs)},{id:1,name:i(Us)},{id:2,name:i(ws)},{id:3,name:i(Ms)}],f={host:[{id:0,name:i(Os)},{id:1,name:i(_s)},{id:2,name:i($s)}],metaservice:b,service:b},{sendRequest:x,sending:y}=fn({request:T2}),g=()=>{x({output:l,performanceData:d,resource:n,statusId:r}).then(()=>{a({message:i(Au),severity:Sn.success}),o()})},u=k=>{s(k.target.value)},A=k=>{p(k.target.value)},w=k=>{m(k.target.value)};return t.createElement($e,{open:!0,confirmDisabled:y,labelCancel:i(dt),labelConfirm:i(Eu),labelTitle:i(Ys),submitting:y,onCancel:e,onClose:e,onConfirm:g},t.createElement(L,{container:!0,direction:"column",spacing:1,style:{minWidth:500}},t.createElement(L,{item:!0},t.createElement($i,{fullWidth:!0,label:i(ca),options:f[n.type],selectedOptionId:r,onChange:u})),t.createElement(L,{item:!0},t.createElement(ie,{fullWidth:!0,ariaLabel:i(Qs),label:i(Qs),value:l,onChange:A})),t.createElement(L,{item:!0},t.createElement(ie,{fullWidth:!0,ariaLabel:i(aa),label:i(aa),value:d,onChange:w}))))},N2=n=>t.createElement(Ae,{color:"primary",size:"small",...n}),$a=({icon:n,label:e,onClick:o,disabled:i})=>{const a=qn();return Boolean(Wb(a.breakpoints.down(1100)))?t.createElement(Rn,{disabled:i,title:e,onClick:o},n):t.createElement(N2,{disabled:i,startIcon:n,variant:"contained",onClick:o},e)},R2=P(n=>({action:{marginRight:n.spacing(1)},flex:{alignItems:"center",display:"flex"}})),G2=({resourcesToCheck:n,selectedResources:e,resourcesToAcknowledge:o,resourcesToSetDowntime:i,resourcesToDisacknowledge:a,setSelectedResources:r,setResourcesToAcknowledge:s,setResourcesToSetDowntime:l,setResourcesToCheck:p,setResourcesToDisacknowledge:d})=>{var vt;const{t:m}=D(),b=R2(),{cancel:f,token:x}=tc(),{showMessage:y}=ae(),[g,u]=t.useState(null),[A,w]=t.useState(),[k,T]=t.useState(),I=An=>y({message:An,severity:Sn.error}),C=An=>y({message:An,severity:Sn.success}),{canAcknowledge:N,canDowntime:H,canCheck:G,canDisacknowledge:R,canSubmitStatus:O,canComment:B}=ht(),U=n.length>0,Q=()=>{r([]),s([]),l([]),p([]),w(null),d([]),T(null)};t.useEffect(()=>{!U||Mh({cancelToken:x,resources:n}).then(()=>{Q(),C(m(Qy))}).catch(()=>I(m(xt)))},[n]),t.useEffect(()=>()=>f(),[]);const $=()=>{s(e)},sn=()=>{l(e)},yn=()=>{p(e)},Cn=()=>{s([])},Vn=()=>{l([])},Tn=()=>{u(null)},Y=()=>{Tn(),d(e)},In=()=>{d([])},Zn=()=>{Tn();const[An]=e;w(An)},En=()=>{w(null)},J=()=>{Tn();const[An]=e;T(An)},F=()=>{T(null)},on=An=>{u(An.currentTarget)},bn=Ei(_e(["status","severity_code"],Be.Ok),e),Nn=!N(e)||bn,co=!H(e),ai=!G(e),ne=!R(e),Ln=e.length!==1||!O(e)||!((vt=it(e))==null?void 0:vt.passive_checks),At=e.length!==1||!B(e);return t.createElement("div",{className:b.flex},t.createElement("div",{className:b.flex},t.createElement("div",{className:b.action},t.createElement($a,{disabled:Nn,icon:t.createElement(ho,null),label:m(to),onClick:$})),t.createElement("div",{className:b.action},t.createElement($a,{disabled:co,icon:t.createElement(jo,null),label:m(ia),onClick:sn})),t.createElement("div",{className:b.action},t.createElement($a,{disabled:ai,icon:t.createElement(Wr,null),label:m(Bo),onClick:yn})),o.length>0&&t.createElement(A2,{resources:o,onClose:Cn,onSuccess:Q}),i.length>0&&t.createElement(_2,{resources:i,onClose:Vn,onSuccess:Q}),a.length>0&&t.createElement(S2,{resources:a,onClose:In,onSuccess:Q}),A&&t.createElement(I2,{resource:A,onClose:En,onSuccess:Q}),k&&t.createElement(Ql,{date:new Date,resource:k,onClose:F,onSuccess:Q})),t.createElement("div",{className:b.flex},t.createElement(Rn,{title:m(_u),onClick:on},t.createElement(Kb,{color:"primary",fontSize:"small"}))),t.createElement(zr,{keepMounted:!0,anchorEl:g,open:Boolean(g),onClose:Tn},t.createElement(et,{disabled:ne,onClick:Y},m(ba)),t.createElement(et,{disabled:Ln,onClick:Zn},m(Ys)),t.createElement(et,{disabled:At,onClick:J},m(xa))))},Ap=["resourcesToCheck","selectedResources","resourcesToAcknowledge","resourcesToSetDowntime","resourcesToDisacknowledge"],D2=le({Component:G2,memoProps:Ap}),H2=["setSelectedResources","setResourcesToAcknowledge","setResourcesToSetDowntime","setResourcesToCheck","setResourcesToDisacknowledge"],F2=()=>{const n=Yn([...Ap,...H2],Hn());return t.createElement(D2,{...n})},P2=({enabledAutorefresh:n,toggleAutorefresh:e})=>{const{t:o}=D(),i=n?Ty:Iy;return t.createElement(Rn,{ariaLabel:o(i),size:"small",title:o(i),onClick:e},n?t.createElement(Yb,null):t.createElement(Qb,null))},L2=({onRefresh:n,enabledAutorefresh:e,setEnabledAutorefresh:o,sending:i})=>{const{t:a}=D(),r=()=>{o(!e)};return t.createElement(L,{container:!0,spacing:1},t.createElement(L,{item:!0},t.createElement(Rn,{ariaLabel:a(sa),disabled:i,size:"small",title:a(sa),onClick:n},t.createElement(Br,null))),t.createElement(L,{item:!0},t.createElement(P2,{enabledAutorefresh:e,toggleAutorefresh:r})))},$2=({onRefresh:n})=>{const{enabledAutorefresh:e,setEnabledAutorefresh:o,sending:i,selectedResourceId:a}=Hn();return re({Component:t.createElement(L2,{enabledAutorefresh:e,sending:i,setEnabledAutorefresh:o,onRefresh:n}),memoProps:[i,e,a]})},M2=({onRefresh:n})=>{const e=qn();return t.createElement(L,{container:!0},t.createElement(L,{item:!0},t.createElement(F2,null)),t.createElement(L,{item:!0,style:{paddingLeft:e.spacing(3)}},t.createElement($2,{onRefresh:n})))},O2=P(()=>({iconButton:{padding:0},tooltip:{backgroundColor:"transparent",maxWidth:"none"}})),vp=({children:n,Chip:e,label:o,onClick:i})=>{const a=O2();return t.createElement(we,{interactive:!0,PopperProps:{onClick:r=>{r.preventDefault(),r.stopPropagation()}},"aria-label":o,classes:{tooltip:a.tooltip},enterDelay:200,enterNextDelay:200,leaveDelay:0,placement:"left",title:n,onClick:r=>{r.preventDefault(),r.stopPropagation(),i==null||i()}},t.createElement("span",null,t.createElement(e,null)))},Ma=n=>n?Bs:Is,zp=({endpoint:n,columns:e})=>{const[o,i]=t.useState(),{sendRequest:a}=fn({request:ce});t.useEffect(()=>{a(n).then(d=>i(d.result))},[]);const r=o===void 0,s=o===null,l=!r&&!s,p=W(K(an("width")),Jb)(e);return t.createElement(Xb,{component:Wn},t.createElement(Zb,{size:"small",style:{width:p}},t.createElement(nx,null,t.createElement(vo,null,e.map(({label:d})=>t.createElement(zo,{key:d},d)))),t.createElement(ex,null,r&&t.createElement(vo,null,t.createElement(zo,{colSpan:e.length},t.createElement($n,{animation:"wave",height:20}))),l&&(o==null?void 0:o.map(d=>t.createElement(vo,{key:d.id},e.map(({label:m,getContent:b,width:f})=>t.createElement(zo,{key:m,style:{maxWidth:f}},t.createElement("span",null,b==null?void 0:b(d))))))),s&&t.createElement(vo,null,t.createElement(zo,{align:"center",colSpan:e.length},t.createElement("span",null,xt))))))},B2=P({comment:{display:"block",overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}}),U2=({endpoint:n})=>{const e=B2(),{t:o}=D(),{toDateTime:i}=Pn(),a=[{getContent:({author_name:r})=>r,id:"author",label:o(hs),type:en.string,width:100},{getContent:({entry_time:r})=>i(r),id:"entry_time",label:o(J0),type:en.string,width:150},{getContent:({is_persistent_comment:r})=>o(Ma(r)),id:"is_persistent",label:o(Ey),type:en.string,width:100},{getContent:({is_sticky:r})=>o(Ma(r)),id:"is_sticky",label:o(zy),type:en.string,width:100},{getContent:({comment:r})=>t.createElement("span",{className:e.comment},Ii(Ni.sanitize(r))),id:"comment",label:o(ye),type:en.string,width:250}];return t.createElement(zp,{columns:a,endpoint:n})},V2=P({comment:{display:"block",overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"}}),j2=({endpoint:n})=>{const e=V2(),{t:o}=D(),{toDateTime:i}=Pn(),a=[{getContent:({author_name:r})=>r,id:"author",label:o(hs),type:en.string,width:100},{getContent:({is_fixed:r})=>o(Ma(r)),id:"is_fixed",label:o(oa),type:en.string,width:100},{getContent:({start_time:r})=>i(r),id:"start_time",label:o(Ps),type:en.string,width:150},{getContent:({end_time:r})=>i(r),id:"end_time",label:o(Es),type:en.string,width:150},{getContent:({comment:r})=>t.createElement("span",{className:e.comment},Ii(Ni.sanitize(r))),id:"comment",label:o(ye),type:en.string,width:250}];return t.createElement(zp,{columns:a,endpoint:n})},Sp=({endpoint:n,Chip:e,DetailsTable:o,label:i})=>t.createElement(vp,{Chip:e,label:i},t.createElement(o,{endpoint:n})),q2=({resource:n})=>{const e=mn(["links","endpoints","downtime"],n);return t.createElement(Sp,{Chip:ka,DetailsTable:j2,endpoint:e,label:`${n.name} ${Ss}`})},W2=({resource:n})=>{const e=mn(["links","endpoints","acknowledgement"],n);return t.createElement(Sp,{Chip:Ea,DetailsTable:U2,endpoint:e,label:`${n.name} ${gs}`})},K2=({row:n})=>t.createElement(L,{container:!0,justify:"center",spacing:1},n.in_downtime&&t.createElement(L,{item:!0},t.createElement(q2,{resource:n})),n.acknowledged&&t.createElement(L,{item:!0},t.createElement(W2,{resource:n}))),Y2=tx(()=>({column:{display:"flex",justifyContent:"center",width:"100%"}})),Zo=({children:n})=>{const e=Y2();return t.createElement("div",{className:e.column},n)},Q2=P(n=>({graph:{display:"block",maxHeight:280,overflow:"auto",padding:n.spacing(2),width:575}})),J2=({onClick:n})=>({row:o})=>{const i=Q2(),a=mn(["links","endpoints","performance_graph"],o);return E(a)?null:t.createElement(Zo,null,t.createElement(vp,{Chip:()=>t.createElement(Rn,{ariaLabel:oo,title:oo,onClick:()=>n(o)},t.createElement(Ur,{fontSize:"small"})),label:oo},t.createElement(Wn,{className:i.graph},t.createElement(sp,{displayTitle:!1,endpoint:a,graphHeight:150,resource:o,timeline:[]}))))},Cp=({endpoint:n,title:e,icon:o})=>{const{t:i}=D();return E(n)||kn(n)?null:t.createElement(Zo,null,t.createElement("a",{href:n,onClick:a=>{a.stopPropagation()}},t.createElement(Rn,{ariaLabel:e,title:i(e||zu),onClick:()=>null},o)))},X2=({row:n})=>{const e=mn(["links","externals","notes","url"],n),o=mn(["links","externals","notes","label"],n);return t.createElement(Cp,{endpoint:e,icon:t.createElement(ox,{fontSize:"small"}),title:o||e})},Z2=({row:n})=>{const e=mn(["links","externals","action_url"],n);return t.createElement(Cp,{endpoint:e,icon:t.createElement(ix,{fontSize:"small"}),title:e})},Tp=P(n=>({actions:{alignItems:"center",display:"flex",flexWrap:"nowrap",gridGap:n.spacing(.75),justifyContent:"center"},statusColumn:{alignItems:"center",display:"flex",width:"100%"}})),n_=({actions:n,row:e})=>{const o=Tp(),{t:i}=D(),{canAcknowledge:a,canDowntime:r,canCheck:s}=ht(),l=_e(["status","severity_code"],Be.Ok,e),p=!a([e])||l,d=!r([e]),m=!s([e]);return t.createElement("div",{className:o.actions},t.createElement(Rn,{ariaLabel:`${i(to)} ${e.name}`,color:"primary",disabled:p,title:i(to),onClick:()=>n.onAcknowledge(e)},t.createElement(ho,{fontSize:"small"})),t.createElement(Rn,{ariaLabel:`${i(by)} ${e.name}`,disabled:d,title:i(ia),onClick:()=>n.onDowntime(e)},t.createElement(jo,{fontSize:"small"})),t.createElement(Rn,{ariaLabel:`${i(Bo)} ${e.name}`,disabled:m,title:i(Bo),onClick:()=>n.onCheck(e)},t.createElement(Wr,{fontSize:"small"})))},e_=({actions:n,t:e})=>({row:i,isHovered:a})=>{const r=Tp(),s=i.status.name;return t.createElement("div",{className:r.statusColumn},a?t.createElement(n_,{actions:n,row:i}):t.createElement(Ue,{label:e(s),severityCode:i.status.severity_code,style:{height:20,width:"100%"}}))},t_=P(n=>({extraSmallChipContainer:{height:19},smallChipLabel:{padding:n.spacing(.5)}})),Ip=({label:n})=>{const e=t_();return t.createElement(Ue,{classes:{label:e.smallChipLabel,root:e.extraSmallChipContainer},label:n,severityCode:Be.None})},o_=({row:n})=>{var e;return n.severity_level?t.createElement(Ip,{label:(e=n.severity_level)==null?void 0:e.toString()}):null},i_=({row:n})=>{const e=Rp();return t.createElement("div",{className:e.resourceDetailsCell},n.icon?t.createElement("img",{alt:n.icon.name,height:16,src:n.icon.url,width:16}):t.createElement(Ip,{label:n.short_type}),t.createElement("div",{className:e.resourceNameItem},t.createElement(V,{variant:"body2"},n.name)))},a_=({row:n})=>{var o,i;const e=Rp();return n.parent?t.createElement("div",{className:e.resourceDetailsCell},t.createElement(Ue,{severityCode:((i=(o=n.parent)==null?void 0:o.status)==null?void 0:i.severity_code)||0}),t.createElement("div",{className:e.resourceNameItem},t.createElement(V,{variant:"body2"},n.parent.name))):null},r_=({Icon:n,title:e})=>{const o=t.createElement(n,{color:"primary",fontSize:"small"});return t.createElement(we,{title:e},o)},c_=({row:n})=>{const{t:e}=D();return n.notification_enabled===!1?t.createElement(Zo,null,t.createElement(r_,{Icon:ax,title:e(Wu)})):null},Np=({Icon:n,title:e})=>t.createElement(Zo,null,t.createElement(we,{title:e},t.createElement(n,{color:"primary",fontSize:"small"}))),s_=({row:n})=>{const{t:e}=D();return n.passive_checks===!1&&n.active_checks===!1?t.createElement(Np,{Icon:rx,title:e(ju)}):n.active_checks===!1?t.createElement(Np,{Icon:cx,title:e(qu)}):null},Rp=P(n=>({resourceDetailsCell:{alignItems:"center",display:"flex",flexWrap:"nowrap",padding:n.spacing(0,.5)},resourceNameItem:{marginLeft:n.spacing(1),whiteSpace:"nowrap"}})),Gp=["severity","status","resource","parent_resource","notes_url","action_url","graph","duration","tries","last_check","information","state"],l_=({actions:n,t:e})=>[{Component:o_,getRenderComponentOnRowUpdateCondition:zn,id:"severity",label:"S",sortField:"severity_level",sortable:!0,type:en.component},{Component:e_({actions:n,t:e}),clickable:!0,getRenderComponentOnRowUpdateCondition:zn,hasHoverableComponent:!0,id:"status",label:e(ca),sortField:"status_severity_code",sortable:!0,type:en.component,width:"minmax(100px, max-content)"},{Component:i_,getRenderComponentOnRowUpdateCondition:zn,id:"resource",label:e(Gs),sortField:"name",sortable:!0,type:en.component},{Component:a_,getRenderComponentOnRowUpdateCondition:zn,id:"parent_resource",label:e(Gu),sortField:"parent_name",sortable:!0,type:en.component},{Component:X2,getRenderComponentOnRowUpdateCondition:zn,id:"notes_url",label:e(Vu),sortable:!1,type:en.component},{Component:Z2,getRenderComponentOnRowUpdateCondition:zn,id:"action_url",label:e(Uu),sortable:!1,type:en.component},{Component:J2({onClick:n.onDisplayGraph}),getRenderComponentOnRowUpdateCondition:zn,id:"graph",label:e(oo),sortable:!1,type:en.component},{getFormattedString:({duration:o})=>o,id:"duration",label:e(ks),sortField:"last_status_change",sortable:!0,type:en.string},{getFormattedString:({tries:o})=>o,id:"tries",label:e(Hs),sortable:!0,type:en.string},{getFormattedString:({last_check:o})=>o,id:"last_check",label:e(Ts),sortable:!0,type:en.string},{getFormattedString:W(tt("","information"),Or(`
`),it,Ta),id:"information",label:e(Sy),sortable:!1,type:en.string,width:"minmax(50px, 1fr)"},{Component:K2,getRenderComponentOnRowUpdateCondition:zn,id:"state",label:e(Ls),sortable:!1,type:en.component},{getFormattedString:({alias:o})=>o,id:"alias",label:e(Xs),sortable:!0,type:en.string},{getFormattedString:({fqdn:o})=>o,id:"fqdn",label:e(Js),sortable:!0,type:en.string},{getFormattedString:({monitoring_server_name:o})=>o,id:"monitoring_server_name",label:e(ra),sortable:!0,type:en.string},{Component:c_,getRenderComponentOnRowUpdateCondition:zn,id:"notification",label:e(ma),type:en.component},{Component:s_,getRenderComponentOnRowUpdateCondition:zn,id:"checks",label:e(Bo),type:en.component}],p_=()=>{const{limit:n,page:e,setPage:o,nextSearch:i,setListing:a,sendRequest:r,enabledAutorefresh:s,customFilters:l,loadDetails:p,details:d,selectedResourceId:m,getCriteriaValue:b,filter:f}=Hn(),x=t.useRef(),y=sx(k=>k.intervals.AjaxTimeReloadMonitoring*1e3),g=()=>{const k=b("sort");if(E(k))return;const[T,I]=k;return{[T]:I}},u=()=>{const k=b("search"),T=k?{regex:{fields:["h.name","h.alias","h.address","s.description","name","alias","parent_name","parent_alias","fqdn","information"],value:k}}:void 0,I=C=>{const N=b(C);return N==null?void 0:N.map(an("id"))};r({hostGroupIds:I("host_groups"),limit:n,monitoringServerIds:I("monitoring_servers"),page:e,resourceTypes:I("resource_types"),search:T,serviceGroupIds:I("service_groups"),sort:g(),states:I("states"),statuses:I("statuses")}).then(a),!E(d)&&p()},A=()=>{window.clearInterval(x.current);const k=s?window.setInterval(()=>{u()},y):void 0;x.current=k},w=()=>{E(l)||ln(j(b("search"),i))||(A(),u())};return t.useEffect(()=>{A()},[s,m]),t.useEffect(()=>()=>{clearInterval(x.current)},[]),t.useEffect(()=>{E(e)||w()},[e]),Kr(()=>{e===1&&w(),o(1)},[n,...f.criterias]),{initAutorefreshAndLoad:w}},d_=()=>{const n=qn(),{t:e}=D(),{showMessage:o}=ae(),{listing:i,setLimit:a,page:r,setPage:s,setOpenDetailsTabId:l,setSelectedResourceUuid:p,setSelectedResourceId:d,setSelectedResourceParentId:m,setSelectedResourceType:b,setSelectedResourceParentType:f,selectedResourceUuid:x,setSelectedResources:y,selectedResources:g,setResourcesToAcknowledge:u,setResourcesToSetDowntime:A,setResourcesToCheck:w,sending:k,setCriteria:T,getCriteriaValue:I,selectedColumnIds:C,setSelectedColumnIds:N}=Hn(),{initAutorefreshAndLoad:H}=p_(),G=({sortField:Y,sortOrder:In})=>{T({name:"sort",value:[Y,In]})},R=Y=>{a(Number(Y))},O=Y=>{s(Y+1)},B=({uuid:Y,id:In,type:Zn,parent:En})=>{p(Y),d(In),m(En==null?void 0:En.id),b(Zn),f(En==null?void 0:En.type)},U={color:jn(n.palette.primary.main,.08),condition:({uuid:Y})=>j(Y,x),name:"detailsOpen"},Q=l_({actions:{onAcknowledge:Y=>{u([Y])},onCheck:Y=>{w([Y])},onDisplayGraph:Y=>{l(La),B(Y)},onDowntime:Y=>{A([Y])}},t:e}),$=k,[sn,yn]=I("sort"),Cn=({uuid:Y})=>Y,Vn=()=>{N(Gp)},Tn=Y=>{if(Y.length===0){o({message:e(Qu),severity:Sn.warning});return}N(Y)};return t.createElement(uf,{checkable:!0,actions:t.createElement(M2,{onRefresh:H}),columnConfiguration:{selectedColumnIds:C,sortable:!0},columns:Q,currentPage:(r||1)-1,getId:Cn,limit:i==null?void 0:i.meta.limit,loading:$,memoProps:[i,sn,yn,r,g,x,k],rowColorConditions:[...Wl(n),U],rows:i==null?void 0:i.result,selectedRows:g,sortField:sn,sortOrder:yn,totalRows:i==null?void 0:i.meta.total,onLimitChange:R,onPaginate:O,onResetColumns:Vn,onRowClick:B,onSelectColumns:Tn,onSelectRows:y,onSort:G})},Oa="centreon-resource-status-21.04-",ni=({cachedItem:n,defaultValue:e,onCachedItemUpdate:o,key:i})=>{if(!E(n))return n;const a=localStorage.getItem(i);if(E(a))return e;const r=JSON.parse(a);return o(r),r},ei=({value:n,key:e})=>{localStorage.setItem(e,JSON.stringify(n))},Dp=`${Oa}filter`,Hp=`${Oa}filter-expanded`;let Ba,Ua;const m_=n=>ni({cachedItem:Ba,defaultValue:n,key:Dp,onCachedItemUpdate:e=>{Ba=e}}),b_=n=>{ei({key:Dp,value:n})},x_=()=>{Ba=null},f_=n=>ni({cachedItem:Ua,defaultValue:n,key:Hp,onCachedItemUpdate:e=>{Ua=e}}),g_=n=>{ei({key:Hp,value:n})},y_=()=>{Ua=null},u_=_.object({criterias:_.array(_.object({name:_.string,object_type:_.nullable(_.string),type:_.string,value:_.optional(_.oneOf([_.string,_.array(_.object({id:_.oneOf([_.number,_.string],"FilterCriteriaMultiSelectId"),name:_.string},"FilterCriteriaMultiSelectValue"),"FilterCriteriaValues"),_.tuple([_.string,_.oneOf([_.isExactly("asc"),_.isExactly("desc")],"FilterCriteriaSortOrder")],"FilterCriteriaTuple")],"FilterCriteriaValue"))},"FilterCriterias"),"FilterCriterias"),id:_.number,name:_.string},"CustomFilter"),h_=Rc({entityDecoder:u_,entityDecoderName:"CustomFilter",listingDecoderName:"CustomFilters"}),ti=()=>{const n=m_(Vo),e=Xt(),o=e.filter;if(Array.isArray(o==null?void 0:o.criterias)){const a=e.filter,r=W(K(dx(an("name"))),Lt(lx(px),{}),_i)([gt.criterias,a.criterias]);return{...mx(gl,a),criterias:r}}return n},w_=()=>{const n=f_(!1),e=Xt();return E(e.filterExpanded)?n:e.filterExpanded},__=()=>{const{t:n}=D(),{sendRequest:e,sending:o}=fn({decoder:h_,request:e1}),i=()=>ti().criterias,a=()=>i().find(pn("name","search")),[r,s]=t.useState([]),[l,p]=t.useState(ti()),[d,m]=t.useState(a().value),[b,f]=t.useState(w_()),[x,y]=t.useState(!1),g=()=>e().then(({result:G})=>(s(G.map(Gt(["order"]))),G)),u=({name:G,value:R})=>{const O=zi(pn("name",G))(l.criterias),B=xx(["criterias",O,"value"]);return bx(B,R,l)},A=[Vo,gt,_a,...r];t.useEffect(()=>{g()},[]);const w=({name:G,value:R})=>{p(u({name:G,value:R}))},k=({name:G,value:R})=>{const O=ul(l);p({...u({name:G,value:R}),...!O&&gl})};Kr(()=>{w({name:"search",value:d})},[...Mn(pn("name","search"),l.criterias)]),t.useEffect(()=>{const G=u({name:"search",value:d});b_(G),Do([{name:"filter",value:G}])},[l,d]),t.useEffect(()=>{if(!Xt().fromTopCounter)return;Do([{name:"fromTopCounter",value:!1}]),p(ti());const{criterias:G}=ti(),R=oe(pn("name","search"))(G);m((R==null?void 0:R.value)||"")},[Xt().fromTopCounter]),t.useEffect(()=>()=>{x_(),y_()}),t.useEffect(()=>{Do([{name:"filterExpanded",value:b}]),g_(b)},[b]);const T=u({name:"search",value:d});return{customFilters:r,customFiltersLoading:o,editPanelOpen:x,filter:l,filterExpanded:b,filters:A,getCriteriaValue:G=>{const R=oe(pn("name",G))(l.criterias);if(!E(R))return R.value},getMultiSelectCriterias:()=>{const G=B=>ft[B],R=B=>W(({name:U})=>U,G,E)(B),O=({name:B})=>G(B).sortId;return W(Mn(R),ki(O))(l.criterias)},loadCustomFilters:g,nextSearch:d,setCriteria:w,setCriteriaAndNewFilter:k,setCustomFilters:s,setEditPanelOpen:y,setFilter:p,setNewFilter:()=>{ul(l)||p({criterias:l.criterias,id:"",name:n(io)})},setNextSearch:m,toggleFilterExpanded:()=>{f(!b)},updatedFilter:T}},k_=()=>{const{t:n}=D();return t.createElement(t.Fragment,null,t.createElement("p",{style:{margin:0}},`${n(Zy)}.`),t.createElement("p",{style:{margin:0}},`${n(nu)} : /opt/rh/httpd24/root/etc/httpd/conf.d/10-centreon.conf`),t.createElement("p",{style:{margin:0}},t.createElement(yo,{color:"inherit",href:"https://docs.centreon.com/21.04/en/upgrade/upgrade-from-19-10.html#configure-apache-api-access",target:"_blank"},`( ${n(eu)} )`)))},Fp=`${Oa}column-ids`;let Va;const E_=n=>ni({cachedItem:Va,defaultValue:n,key:Fp,onCachedItemUpdate:e=>{Va=e}}),A_=n=>{ei({key:Fp,value:n})},v_=()=>{Va=null},z_=()=>{const[n,e]=t.useState(),[o,i]=t.useState(30),[a,r]=t.useState(),[s,l]=t.useState(!0),[p,d]=t.useState(E_(Gp));t.useEffect(()=>{A_(p)},[p]),t.useEffect(()=>()=>{v_()});const{sendRequest:m,sending:b}=fn({getErrorMessage:ko(_e(["response","status"],404),rn(k_),Ri(xt,["response","data","message"])),request:fp});return{enabledAutorefresh:s,limit:o,listing:n,page:a,selectedColumnIds:p,sendRequest:m,sending:b,setEnabledAutorefresh:l,setLimit:i,setListing:e,setPage:r,setSelectedColumnIds:d}},S_=()=>{const[n,e]=t.useState([]),[o,i]=t.useState([]),[a,r]=t.useState([]),[s,l]=t.useState([]),[p,d]=t.useState([]);return{resourcesToAcknowledge:o,resourcesToCheck:s,resourcesToDisacknowledge:p,resourcesToSetDowntime:a,selectedResources:n,setResourcesToAcknowledge:i,setResourcesToCheck:l,setResourcesToDisacknowledge:d,setResourcesToSetDowntime:r,setSelectedResources:e}},Pp="centreon-resource-status-details-21.04";let Lp;const C_=n=>ni({cachedItem:Lp,defaultValue:n,key:Pp,onCachedItemUpdate:e=>{Lp=e}}),T_=n=>{ei({key:Pp,value:n})},I_=()=>{const[n,e]=t.useState(Et),[o,i]=t.useState(),[a,r]=t.useState(),[s,l]=t.useState(),[p,d]=t.useState(),[m,b]=t.useState(),[f,x]=t.useState(),[y,g]=t.useState({}),[u,A]=t.useState(C_(550)),{t:w}=D(),{sendRequest:k}=fn({getErrorMessage:ko(_e(["response","status"],404),rn(w(vu)),Ri(w(xt),["response","data","message"])),request:ce}),T=R=>{var O,B;e(Et),i(R.uuid),r(R.id),d(R.type),b((O=R==null?void 0:R.parent)==null?void 0:O.type),l((B=R==null?void 0:R.parent)==null?void 0:B.id)};t.useEffect(()=>{const O=Xt().details;if(E(O))return;const{uuid:B,id:U,parentId:Q,type:$,parentType:sn,tab:yn,tabParameters:Cn}=O;E(yn)||e(g2(yn)),i(B),r(U),l(Q),d($),b(sn),g(Cn||{})},[]),t.useEffect(()=>{Do([{name:"details",value:{id:a,parentId:s,parentType:m,tab:y2(n),tabParameters:y,type:p,uuid:o}}])},[n,a,p,m,m,y]);const I=()=>E(s)?`${Ee}/${p}s/${a}`:`${Ee}/${m}s/${s}/${p}s/${a}`,C=()=>{i(void 0),r(void 0),l(void 0),b(void 0),d(void 0)},N=()=>{E(a)||k(I()).then(x).catch(()=>{C()})};return t.useEffect(()=>{x(void 0),N()},[o]),t.useEffect(()=>{T_(u)},[u]),{clearSelectedResource:C,details:f,getSelectedResourceDetailsEndpoint:I,loadDetails:N,openDetailsTabId:n,panelWidth:u,selectResource:T,selectedResourceId:a,selectedResourceParentId:s,selectedResourceUuid:o,setGraphTabParameters:R=>{g({...y,graph:R})},setOpenDetailsTabId:e,setPanelWidth:A,setSelectedResourceId:r,setSelectedResourceParentId:l,setSelectedResourceParentType:b,setSelectedResourceType:d,setSelectedResourceUuid:i,setServicesTabParameters:R=>{g({...y,services:R})},tabParameters:y}},N_=P(n=>({filterCard:{alignItems:"center",display:"grid",gridAutoFlow:"column",gridGap:n.spacing(2),gridTemplateColumns:"auto 1fr"}})),R_=({filter:n,currentFilter:e,customFilters:o,setFilter:i,setCustomFilters:a,setNewFilter:r})=>{const s=N_(),{t:l}=D(),{showMessage:p}=ae(),[d,m]=t.useState(!1),{sendRequest:b,sending:f}=fn({request:el}),{sendRequest:x,sending:y}=fn({request:i1}),{name:g,id:u}=n,A=te().shape({name:be().required(l(bu))}),w=uo({enableReinitialize:!0,initialValues:{name:g},onSubmit:R=>{const O={...n,name:R.name};b({filter:Gt(["id"],O),id:O.id}).then(()=>{p({message:l(mu),severity:Sn.success}),j(O.id,e.id)&&i(O);const B=zi(pn("id",O.id),o);a(gx(B,O,o))})},validationSchema:A}),k=()=>{m(!0)},T=()=>{m(!1),x(n).then(()=>{p({message:l(du),severity:Sn.success}),j(n.id,e.id)&&r(),a(Mn(j(n),o))})},I=()=>{m(!1)},C=wo(j(!0),[y,f]),N=Ei(j(!0),[w.isValid,w.dirty]),H=()=>{!N||w.submitForm()},G=R=>{R.keyCode===13&&H()};return t.createElement("div",{className:s.filterCard},t.createElement(Yt,{alignCenter:!1,loading:C,loadingIndicatorSize:24},t.createElement(Rn,{title:l(qs),onClick:k},t.createElement(fx,{fontSize:"small"}))),t.createElement(ie,{transparent:!0,ariaLabel:`${l(X0)}-${u}-${l(la)}`,error:w.errors.name,value:w.values.name,onBlur:H,onChange:w.handleChange("name"),onKeyDown:G}),d&&t.createElement(Ki,{open:!0,labelCancel:l(dt),labelConfirm:l(qs),labelTitle:l(pu),onCancel:I,onConfirm:T}))},G_=["filter","currentFilter","customFilters"],D_=le({Component:R_,memoProps:G_}),H_=({filter:n})=>{const{setFilter:e,filter:o,setCustomFilters:i,customFilters:a,setNewFilter:r}=Hn();return t.createElement(D_,{currentFilter:o,customFilters:a,filter:n,setCustomFilters:i,setFilter:e,setNewFilter:r})},F_=P(n=>({container:{width:"100%"},filterCard:{alignItems:"center",display:"grid",gridGap:n.spacing(2),gridTemplateColumns:"1fr auto",padding:n.spacing(1)},filters:{display:"grid",gridAutoFlow:"row",gridGap:n.spacing(3),gridTemplateRows:"1fr",width:"100%"},header:{alignItems:"center",display:"flex",height:"100%",justifyContent:"center"},loadingIndicator:{height:n.spacing(1),marginBottom:n.spacing(1),width:"100%"}})),P_=()=>{const n=F_(),{t:e}=D(),{customFilters:o,setEditPanelOpen:i,setCustomFilters:a}=Hn(),{sendRequest:r,sending:s}=fn({request:o1}),l=()=>{i(!1)},p=({draggableId:b,source:f,destination:x})=>{const y=Number(b);if(E(x))return;const g=kx(f.index,x.index,o);a(g),r({id:y,order:x.index})},d=[{expandable:!1,id:"edit",section:t.createElement("div",{className:n.container},t.createElement("div",{className:n.loadingIndicator},s&&t.createElement(yx,{style:{width:"100%"}})),t.createElement(ux,{onDragEnd:p},t.createElement(hx,{droppableId:"droppable"},b=>t.createElement("div",{className:n.filters,ref:b.innerRef,...b.droppableProps},o==null?void 0:o.map((f,x)=>t.createElement(wx,{draggableId:`${f.id}`,index:x,key:f.id},y=>t.createElement(Wn,{square:!0,className:n.filterCard,ref:y.innerRef,...y.draggableProps},t.createElement(H_,{filter:f}),t.createElement("div",{...y.dragHandleProps},t.createElement(_x,null))))),b.placeholder))))}],m=t.createElement("div",{className:n.header},t.createElement(V,{align:"center",variant:"h6"},e(js)));return t.createElement(eg,{header:m,memoProps:[o],sections:d,onClose:l})},L_=({editPanelOpen:n,selectedResourceId:e})=>t.createElement(wc,{open:n,panel:t.createElement(P_,null)},t.createElement(_f,{filters:t.createElement(j1,null),listing:t.createElement(d_,null),panel:t.createElement(Gh,null),panelOpen:!E(e)})),$_=["editPanelOpen","selectedResourceId"],M_=le({Component:L_,memoProps:$_}),O_=()=>{const n=z_(),e=__(),o=I_(),i=S_(),{selectedResourceId:a}=o;return t.createElement(fs.Provider,{value:{...n,...e,...o,...i}},t.createElement(M_,{editPanelOpen:e.editPanelOpen,selectedResourceId:a}))};var B_=s3(O_);const ja=({checked:n,error:e,label:o,info:i,className:a,...r})=>t.createElement("div",{className:M(v["custom-control"],v["custom-radio"],v["form-group"])},t.createElement("input",{info:!0,"aria-checked":n,className:v["form-check-input"],type:"radio",...no(r)}),t.createElement("label",{className:v["custom-control-label"],htmlFor:r.id},o,i),e?t.createElement("div",{className:v["invalid-feedback"]},t.createElement("div",{className:M(v.field__msg,v["field__msg--error"])},e)):null);ja.displayName="RadioField",ja.defaultProps={className:v.field};var U_=je(ja);const $p=n=>n.value?n.value:n,V_=n=>n.label?n.label:n,j_=n=>n.info?n.info:null,oi=({options:n,className:e,label:o,meta:i,...a})=>{const{t:r}=D(),{error:s,touched:l,...p}=i,d=m=>n.map((b,f)=>t.createElement(U_,{key:f,...m,checked:$p(b)===m.input.value,className:v["radio-group-field__radio"],info:j_(b),label:r(V_(b)),value:$p(b)}));return t.createElement("div",{className:v["form-group"]},d({...a,meta:{...p}}),l&&s?t.createElement(na,null,s):null)};oi.displayName="RadioGroupField",oi.propTypes={options:tn.array.isRequired},oi.defaultProps={className:v["radio-group-field"]};const q_=[{label:"Add a Centreon Remote Server",value:1},{label:"Add a Centreon Poller",value:2}],W_=({error:n,handleSubmit:e,onSubmit:o})=>{const{t:i}=D();return t.createElement("div",{className:M(v["form-wrapper"],v.small)},t.createElement("div",{className:v["form-inner"]},t.createElement("div",{className:v["form-heading"]},t.createElement("h2",{className:v["form-title"]},i("Server Configuration Wizard")),t.createElement("p",{className:v["form-text"]},i("Choose a server type"))),t.createElement("form",{autoComplete:"off",onSubmit:e(o)},t.createElement(X,{component:oi,name:"server_type",options:q_}),t.createElement("div",{className:v["form-buttons"]},t.createElement("button",{className:v.button,type:"submit"},i("Next"))),n?t.createElement("div",{className:v["error-block"]},n.message):null)))},K_=()=>({});var Y_=Nt({form:"ServerConfigurationWizardForm",validate:K_})(W_);class Q_ extends t.Component{constructor(){super(...arguments);h(this,"links",[{active:!0,number:1,path:cn.serverConfigurationWizard},{active:!1,number:2},{active:!1,number:3},{active:!1,number:4}]);h(this,"handleSubmit",({server_type:e})=>{const{history:o}=this.props;e==="1"&&o.push(cn.remoteServerStep1),e==="2"&&o.push(cn.pollerStep1)})}render(){const{links:e}=this;return t.createElement(Ke,null,t.createElement(We,{links:e}),t.createElement(Y_,{onSubmit:this.handleSubmit.bind(this)}))}}class J_ extends t.Component{constructor(){super(...arguments);h(this,"state",{deleteToggled:!1,deletingEntity:!1,extensionDetails:!1,extensions:{module:{entities:[]},widget:{entities:[]}},extensionsInstallingStatus:{},extensionsUpdatingStatus:{},installed:!1,modalDetailsActive:!1,modalDetailsLoading:!1,modalDetailsType:"module",modulesActive:!1,not_installed:!1,search:"",updated:!1,widgetsActive:!1});h(this,"componentDidMount",()=>{this.getData()});h(this,"onChange",(e,o)=>{const{filters:i}=this.state,a={};typeof this.state[o]!="undefined"&&(a[o]=e),this.setState({...a,filters:{...i,[o]:e}},this.getData)});h(this,"clearFilters",()=>{this.setState({installed:!1,modulesActive:!1,not_installed:!1,nothingShown:!1,search:"",updated:!1,widgetsActive:!1},this.getData)});h(this,"getEntitiesByKeyAndVersionParam",(e,o,i,a)=>{const{extensions:r}=this.state,s=[];if(r){const{status:l,result:p}=r;if(l)for(let d=0;d<p[i].entities.length;d++){const m=p[i].entities[d];m.version[e]===o&&s.push({id:m.id,type:i})}}a(s)});h(this,"getAllEntitiesByVersionParam",(e,o,i)=>{const{modulesActive:a,widgetsActive:r}=this.state;!a&&!r||a&&r?this.getEntitiesByKeyAndVersionParam(e,o,"module",s=>{this.getEntitiesByKeyAndVersionParam(e,o,"widget",l=>{i&&i([...s,...l])})}):a?this.getEntitiesByKeyAndVersionParam(e,o,"module",s=>{i&&i([...s])}):r&&this.getEntitiesByKeyAndVersionParam(e,o,"widget",s=>{i&&i([...s])})});h(this,"runActionOnAllEntities",(e,o,i)=>{this.getAllEntitiesByVersionParam(e,o,a=>{this.setStatusesByIds(a,i,()=>{e==="outdated"?this.updateOneByOne(a):e==="installed"&&this.installOneByOne(a)})})});h(this,"reloadNavigation",()=>{const{reloadNavigation:e}=this.props;e()});h(this,"reloadExternalComponents",()=>{const{reloadExternalComponents:e}=this.props;e()});h(this,"setStatusesByIds",(e,o,i)=>{let a=this.state[o];for(const{id:r}of e)a={...a,[r]:!0};this.setState({[o]:a},i)});h(this,"updateOneByOne",e=>{if(e.length>0){const o=e.shift();this.updateById(o.id,o.type,()=>{this.updateOneByOne(e)})}});h(this,"installOneByOne",e=>{if(e.length>0){const o=e.shift();this.installById(o.id,o.type,()=>{this.installOneByOne(e)})}});h(this,"setStatusByKey",(e,o,i)=>{this.setState({[e]:{...this.state[e],[o]:!1}},()=>{i&&typeof i=="function"&&i()})});h(this,"runAction",(e,o,i,a,r)=>{this.setStatusesByIds([{id:i}],e,()=>{wn(`internal.php?object=centreon_module&action=${o}&id=${i}&type=${a}`).post().then(()=>{this.getData(()=>{this.setStatusByKey(e,i,r),this.reloadNavigation()})}).catch(s=>{throw this.getData(()=>{this.setStatusByKey(e,i,r),this.reloadNavigation()}),s})})});h(this,"installById",(e,o,i)=>{const{modalDetailsActive:a}=this.state;a?(this.setState({modalDetailsLoading:!0}),this.runAction("extensionsInstallingStatus","install",e,o,()=>{this.getExtensionDetails(e,o)})):this.runAction("extensionsInstallingStatus","install",e,o,i)});h(this,"updateById",(e,o,i)=>{const{modalDetailsActive:a}=this.state;a?(this.setState({modalDetailsLoading:!0}),this.runAction("extensionsUpdatingStatus","update",e,o,()=>{this.getExtensionDetails(e,o)})):this.runAction("extensionsUpdatingStatus","update",e,o,i)});h(this,"deleteById",(e,o)=>{const{modalDetailsActive:i}=this.state;this.setState({deleteToggled:!1,deletingEntity:!1,modalDetailsLoading:i},()=>{wn("internal.php?object=centreon_module&action=remove").delete("",{params:{id:e,type:o}}).then(()=>{this.getData(),this.reloadNavigation(),i&&this.getExtensionDetails(e,o)})})});h(this,"toggleDeleteModal",(e,o)=>{const{deleteToggled:i}=this.state;this.setState({deleteToggled:!i,deletingEntity:e?{...e,type:o}:!1})});h(this,"getParsedGETParamsForExtensions",e=>{const{installed:o,not_installed:i,updated:a,search:r}=this.state;let s="";const l=!1;r&&(s+=`&search=${r}`),o&&i&&a||!o&&!i&&!a||(a&&(s+="&updated=false"),!o&&i?s+="&installed=false":o&&!i&&(s+="&installed=true")),e(s,l)});h(this,"getData",e=>{this.getParsedGETParamsForExtensions((o,i)=>{this.setState({nothingShown:i}),i||wn(`internal.php?object=centreon_module&action=list${o}`).get().then(({data:a})=>{this.setState({extensions:a},()=>{e&&typeof e=="function"&&e()})})})});h(this,"hideExtensionDetails",()=>{this.setState({modalDetailsActive:!1,modalDetailsLoading:!1})});h(this,"activateExtensionsDetails",(e,o)=>{this.setState({modalDetailsActive:!0,modalDetailsLoading:!0,modalDetailsType:o},()=>{this.getExtensionDetails(e,o)})});h(this,"getExtensionDetails",(e,o)=>{wn(`internal.php?object=centreon_module&action=details&type=${o}&id=${e}`).get().then(({data:i})=>{const{result:a}=i;a.images&&(a.images=a.images.map(r=>`./${r}`)),this.setState({extensionDetails:a,modalDetailsLoading:!1})})});h(this,"getEntityByIdAndType",(e,o)=>{const{extensions:i}=this.state;return o==="module"?i.result.module.entities.find(a=>a.id===e):i.result.widget.entities.find(a=>a.id===e)});h(this,"toggleDeleteModalByIdAndType",(e,o)=>{const i=this.getEntityByIdAndType(e,o);this.toggleDeleteModal(i,o)})}render(){const{extensions:e,modulesActive:o,deleteToggled:i,widgetsActive:a,not_installed:r,installed:s,updated:l,search:p,nothingShown:d,modalDetailsActive:m,modalDetailsLoading:b,modalDetailsType:f,extensionsUpdatingStatus:x,extensionsInstallingStatus:y,deletingEntity:g,extensionDetails:u}=this.state,A=(s&&r&&l||!s&&!r&&!l)&&p.length===0&&(o&&a||!o&&!a);return t.createElement("div",null,t.createElement(If,{fullText:{filterKey:"search",label:"Search",value:p},switches:[[{customClass:"container__col-md-4 container__col-xs-4",filterKey:"not_installed",switchStatus:"Not installed",switchTitle:"Status",value:r},{customClass:"container__col-md-4 container__col-xs-4",filterKey:"installed",switchStatus:"Installed",value:s},{customClass:"container__col-md-4 container__col-xs-4",filterKey:"updated",switchStatus:"Outdated",value:l}],[{customClass:"container__col-sm-3 container__col-xs-4",filterKey:"modulesActive",switchStatus:"Module",switchTitle:"Type",value:o},{customClass:"container__col-sm-3 container__col-xs-4",filterKey:"widgetsActive",switchStatus:"Widget",value:a},{button:!0,buttonType:"bordered",color:"black",label:"Clear Filters",onClick:this.clearFilters.bind(this)}]],onChange:this.onChange.bind(this)}),t.createElement(ji,null,t.createElement(ze,{buttonType:"regular",color:"orange",customClass:"mr-2",label:`${A?"Update all":"Update selection"}`,style:{opacity:"1"},onClick:this.runActionOnAllEntities.bind(this,"outdated",!0,"extensionsUpdatingStatus")}),t.createElement(ze,{buttonType:"regular",color:"green",customClass:"mr-2",label:`${A?"Install all":"Install selection"}`,onClick:this.runActionOnAllEntities.bind(this,"installed",!1,"extensionsInstallingStatus")}),t.createElement(jc,{path:"/administration/extensions/manager"})),e.result&&!d?t.createElement(t.Fragment,null,e.result.module&&(o||!o&&!a)?t.createElement(Ec,{entities:e.result.module.entities,installing:y,title:"Modules",type:"module",updating:x,onCardClicked:this.activateExtensionsDetails,onDelete:this.toggleDeleteModal,onInstall:this.installById,onUpdate:this.updateById}):null,e.result.widget&&(a||!o&&!a)?t.createElement(Ec,{entities:e.result.widget.entities,hrColor:"blue",hrTitleColor:"blue",installing:y,title:"Widgets",titleColor:"blue",type:"widget",updating:x,onCardClicked:this.activateExtensionsDetails,onDelete:this.toggleDeleteModal,onInstall:this.installById,onUpdate:this.updateById}):null):null,u&&m?t.createElement(Hf,{loading:b,modalDetails:u,type:f,onCloseClicked:this.hideExtensionDetails.bind(this),onDeleteClicked:this.toggleDeleteModalByIdAndType,onInstallClicked:this.installById,onUpdateClicked:this.updateById}):null,i?t.createElement(Ff,{deletingEntity:g,onCancel:this.toggleDeleteModal,onConfirm:this.deleteById}):null)}}const X_=n=>({reloadNavigation:()=>{n(Ex([Pc(),Mc()]))}});var Z_=_n(null,X_)(J_),n5=`/* Colors */
/* Fonts */
.message-alert {
  background-color: #ffe6ec;
  border: 1px solid #e00b3d;
  color: #e00b3d;
  font-weight: bold;
  margin: 5px auto;
  padding: 4px;
  text-align: center;
  border-radius: 4px;
  width: 30%;
  font-family: "Roboto Regular";
  font-size: 11px;
  box-sizing: border-box;
  margin-top: 15px;
}`;const qa=()=>t.createElement("div",{className:n5["message-alert"]},"You are not allowed to see this page"),e5=[{comp:c0,path:cn.pollerStep1},{comp:b0,path:cn.pollerStep2},{comp:y0,path:cn.pollerStep3},{comp:E0,path:cn.remoteServerStep1},{comp:T0,path:cn.remoteServerStep2},{comp:G0,path:cn.remoteServerStep3},{comp:Q_,path:cn.serverConfigurationWizard},{comp:Z_,path:cn.extensionsManagerPage},{comp:qa,path:cn.notAllowedPage},{comp:B_,path:cn.resources}],Mp=n=>n.is_react?n.url:`/main.php?p=${n.page}${n.options!==null?n.options:""}`,ii=n=>{if(n.url)return Mp(n);if(n.groups){const e=n.groups.find(ii);return e&&e.children?Op(e):void 0}return n.children?Op(n):void 0},Op=n=>{if(!n.children)return;const e=n.children.find(ii);return e?ii(e):void 0},Wa=n=>{const e=n.url?Mp(n):ii(n);return e?{label:n.label,link:e}:null},t5=n=>n.navigation.items,o5=go(t5,n=>{const e={};return n.forEach(o=>{const i=Wa(o);i!==null&&(e[i.link]=[{label:i.label,link:i.link}],o.children&&o.children.forEach(a=>{const r=Wa(a);r!==null&&(e[r.link]=[{label:i.label,link:i.link},{label:r.label,link:r.link}],a.groups&&a.groups.forEach(s=>{s.children&&s.children.forEach(l=>{const p=Wa(l);p!==null&&(e[p.link]=[{label:i.label,link:i.link},{label:r.label,link:r.link},{label:p.label,link:p.link}])})}))}))}),e}),i5=P(()=>({link:{"&:hover":{textDecoration:"underline"},color:"inherit",fontSize:"small",textDecoration:"none"}})),a5=({last:n,breadcrumb:e})=>{const o=i5();return t.createElement(yo,{className:o.link,color:n?"textPrimary":"inherit",component:gn,to:e.link},e.label)},r5=P({item:{display:"flex"},root:{padding:"4px 16px"}}),Bp=({breadcrumbsByPath:n,path:e})=>{if(n[e])return n[e];if(e.includes("/")){const o=e.split("/").slice(0,-1).join("/");return Bp({breadcrumbsByPath:n,path:o})}return[]},c5=({breadcrumbsByPath:n,path:e})=>{const o=r5(),i=t.useMemo(()=>Bp({breadcrumbsByPath:n,path:e}),[n,e]);return t.createElement(Ax,{"aria-label":"Breadcrumb",classes:{li:o.item,root:o.root},separator:t.createElement(vx,{fontSize:"small"})},i.map((a,r)=>t.createElement(a5,{breadcrumb:a,key:a.label,last:r===i.length-1})))},s5=n=>({breadcrumbsByPath:o5(n)});var Up=_n(s5)(c5);const Vp=Tr("div")(({theme:n})=>({background:n.palette.background.default,display:"grid",gridTemplateRows:"auto 1fr",height:"100%",overflow:"auto"})),l5=({history:n,allowedPages:e,pages:o})=>{const i=n.createHref({hash:"",pathname:"/",search:""}),a=Object.entries(o),r=l=>e.find(p=>l.includes(p));return a.filter(([l])=>r(l)).map(([l,p])=>{const d=t.lazy(()=>Vc(i,p));return t.createElement(at,{exact:!0,key:l,path:l,render:m=>t.createElement(Vp,null,t.createElement(Up,{path:l}),t.createElement(d,{...m}))})})},p5=t.memo(({allowedPages:n,history:e,pages:o,externalPagesFetched:i})=>kn(n)?t.createElement(Kt,null):t.createElement(t.Suspense,{fallback:t.createElement(Kt,null)},t.createElement(Yr,null,e5.map(({path:a,comp:r,...s})=>t.createElement(at,{exact:!0,key:a,path:a,render:l=>t.createElement(Vp,null,n.includes(a)?t.createElement(t.Fragment,null,t.createElement(Up,{path:a}),t.createElement(r,{...l})):t.createElement(qa,{...l})),...s})),l5({allowedPages:n,history:e,pages:o}),i&&t.createElement(at,{component:qa}))),(n,e)=>j(n.pages,e.pages)&&j(n.allowedPages,e.allowedPages)&&j(n.externalPagesFetched,e.externalPagesFetched)),d5=n=>({allowedPages:Zi(n),externalPagesFetched:n.externalComponents.fetched,pages:n.externalComponents.pages});var m5=_n(d5)(xi(p5));const b5=({history:{location:{key:n}}})=>t.createElement(t.Suspense,{fallback:t.createElement(Kt,null)},t.createElement(Yr,null,t.createElement(at,{exact:!0,component:K6,key:`path-${n}`,path:"/main.php"}),t.createElement(at,{exact:!0,path:"/",render:()=>t.createElement(Redirect,{to:"/main.php"})}),t.createElement(at,{component:m5,path:"/"})));var x5=xi(b5);const f5=Ix({content:{display:"flex",flexDirection:"column",height:" 100vh",overflow:"hidden",position:"relative",transition:"all 0.3s",width:"100%"},fullScreenWrapper:{flexGrow:1,height:"100%",overflow:"hidden",width:"100%"},mainContent:{backgroundcolor:"white",height:"100%",width:"100%"},wrapper:{alignItems:"stretch",display:"flex",height:"100%",overflow:"hidden"}});class g5 extends t.Component{constructor(){super(...arguments);this.state={isFullscreenEnabled:!1},this.keepAliveTimeout=null,this.getMinArgument=()=>{const{search:e}=Ve.location;return Sx.parse(e).min==="1"},this.goFull=()=>{window.fullscreenSearch=window.location.search,window.fullscreenHash=window.location.hash,setTimeout(()=>{this.setState({isFullscreenEnabled:!0})},200)},this.removeFullscreenParams=()=>{Ve.location.pathname==="/main.php"&&Ve.push({hash:window.fullscreenHash,pathname:"/main.php",search:window.fullscreenSearch}),window.fullscreenSearch=null,window.fullscreenHash=null},this.keepAlive=()=>{this.keepAliveTimeout=setTimeout(()=>{wn("internal.php?object=centreon_keepalive&action=keepAlive").get().then(()=>this.keepAlive()).catch(e=>{e.response&&e.response.status===401?window.location.href="index.php?disconnect=1":this.keepAlive()})},15e3)}}componentDidMount(){this.props.fetchExternalComponents(),this.keepAlive()}render(){const e=this.getMinArgument(),{classes:o}=this.props;return t.createElement(t.Suspense,{fallback:t.createElement(Xi,null)},t.createElement(Cx,{history:Ve},t.createElement(Lf,null,t.createElement("div",{className:o.wrapper},!e&&t.createElement(U6,null),t.createElement(q6,null),t.createElement("div",{className:o.content,id:"content"},!e&&t.createElement(D6,null),t.createElement("div",{className:o.fullScreenWrapper,id:"fullscreen-wrapper"},t.createElement(Tx,{enabled:this.state.isFullscreenEnabled,onChange:i=>{this.setState({isFullscreenEnabled:i})},onClose:this.removeFullscreenParams},t.createElement("div",{className:o.mainContent},t.createElement(x5,null)))),!e&&t.createElement(W6,null)),t.createElement("span",{className:Qn["full-screen"],onClick:this.goFull})))))}}const y5=n=>({fetchExternalComponents:()=>{n(Mc())}});var u5=_n(null,y5)(zx(f5)(g5));const h5="./api/internal.php",w5=`${h5}?object=centreon_i18n&action=translation`,Ka="./api/beta",_5=`${Ka}/configuration/users/current/parameters`,k5=`${Ka}/administration/parameters`,E5=`${Ka}/users/acl/actions`;xn.extend(Nx),xn.extend(Rx),xn.extend(Gx);const A5=o6(),v5=()=>{const{user:n,setUser:e}=Dx(),{downtime:o,setDowntime:i}=Hx(),{refreshInterval:a,setRefreshInterval:r}=Fx(),{actionAcl:s,setActionAcl:l}=Px(),[p,d]=t.useState(!1),{sendRequest:m}=fn({request:ce}),{sendRequest:b}=fn({request:ce}),{sendRequest:f}=fn({request:ce}),{sendRequest:x}=fn({request:ce}),y=({retrievedUser:g,retrievedTranslations:u})=>{var w;const A=(w=g.locale||navigator.language)==null?void 0:w.slice(0,2);Mx.use(Ox).init({fallbackLng:"en",keySeparator:!1,lng:A,nsSeparator:!1,resources:W(hi,Lt((k,[T,I])=>Bx([k,{[T]:{translation:I}}]),{}))(u)})};return t.useEffect(()=>{Promise.all([m(_5),b(k5),f(w5),x(E5)]).then(([g,u,A,w])=>{e({alias:g.alias,locale:g.locale||"en",name:g.name,timezone:g.timezone}),i({default_duration:parseInt(u.monitoring_default_downtime_duration,10)}),r(parseInt(u.monitoring_default_refresh_interval,10)),l(w),y({retrievedTranslations:A,retrievedUser:g}),d(!0)}).catch(g=>{_e(["response","status"],401)(g)&&(window.location.href="index.php?disconnect=1")})},[]),p?t.createElement(Lx.Provider,{value:{...n,acl:{actions:s},downtime:o,refreshInterval:a}},t.createElement($x,{store:A5},t.createElement(t.Suspense,{fallback:t.createElement(Xi,null)},t.createElement(u5,null)))):t.createElement(Xi,null)};Ux.render(t.createElement(v5,null),document.getElementById("root"));
