var e=Object.defineProperty,t=(t,r,a)=>(((t,r,a)=>{r in t?e(t,r,{enumerable:!0,configurable:!0,writable:!0,value:a}):t[r]=a})(t,"symbol"!=typeof r?r+"":r,a),a);import{r}from"./vendor.ecb5856a.js";import{n as a}from"./index.eda796bc.js";import{W as s}from"./index.bcbbb51c.js";import{B as i,P as n}from"./baseWizard.5f66a05d.js";import{b as o}from"./withTranslation.73eadfaa.js";import"./index.169497e9.js";class l extends r.Component{constructor(){super(...arguments),t(this,"state",{generateStatus:null}),t(this,"links",[{active:!0,prevActive:!0,number:1},{active:!0,prevActive:!0,number:2},{active:!0,prevActive:!0,number:3},{active:!0,number:4}])}render(){const{links:e}=this,{pollerData:t,t:a}=this.props,{generateStatus:o}=this.state;return r.createElement(i,null,r.createElement(n,{links:e}),r.createElement(s,{statusCreating:t.submitStatus,statusGenerating:o,formTitle:`${a("Finalizing Setup")}:`}))}}var m=o()(a((({pollerForm:e})=>({pollerData:e})),{})(l));export default m;
