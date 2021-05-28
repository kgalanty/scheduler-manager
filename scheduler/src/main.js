import Vue from 'vue'
import Buefy from 'buefy'
import router from './router'
import store from './store'
import App from './App'
import 'buefy/dist/buefy.css'
import moment from 'moment'
import axios from 'axios'
Vue.prototype.moment = moment
Vue.prototype.$http = axios
import { library } from '@fortawesome/fontawesome-svg-core';
// internal icons
import { fas } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

library.add(fas);
Vue.component('vue-fontawesome', FontAwesomeIcon);
Vue.prototype.$filterObject = (obj, filter, filterValue) => 
Object.keys(obj).reduce((acc, val) => 
(obj[val][filter] === filterValue ? {
    ...acc,
    [val]: obj[val]
}:   acc                               
), {});
Vue.use(Buefy, {
  defaultIconComponent: "vue-fontawesome",
  defaultIconPack: "fas",
  customIconPacks: {
    fas: {
      sizes: {
        default: "lg",
        "is-small": "1x",
        "is-medium": "2x",
        "is-large": "3x"
      },
      iconPrefix: ""
    }
  }
})
Vue.config.productionTip = false

new Vue({
  render: h => h(App),
  router,
  store,
  components: { App }
}).$mount('#app')

// var dragged;

//   /* events fired on the draggable target */
//   document.addEventListener("drag", function( ) {

//   }, false);

//   document.addEventListener("dragstart", function( event ) {
//       // store a ref. on the dragged elem
//       dragged = event.target;
//       // make it half transparent
//       event.target.style.border = '2px solid black';
//       }, false);

//   document.addEventListener("dragend", function( event ) {
//       // reset the transparency
//       event.target.style.opacity = "";
//   }, false);

//   /* events fired on the drop targets */
//   document.addEventListener("dragover", function( event ) {
//       // prevent default to allow drop
//       event.preventDefault();
//   }, false);

//   document.addEventListener("dragenter", function( event ) {
//       // highlight potential drop target when the draggable element enters it
//       if ( event.target.className == "dropzone" ) {
//           event.target.style.background = "purple";
//       }

//   }, false);

//   document.addEventListener("dragleave", function( event ) {
//       // reset background of potential drop target when the draggable element leaves it
//       if ( event.target.className == "dropzone" ) {
//           event.target.style.background = "";
//       }

//   }, false);

//   document.addEventListener("drop", function( event ) {
//       // prevent default action (open as link for some elements)
//       event.preventDefault();
//       // move dragged elem to the selected drop target
//       if ( event.target.className == "dropzone" ) {
//           event.target.style.background = "";
//           dragged.parentNode.removeChild( dragged );
//           event.target.appendChild( dragged );
//       }

//   }, false);