// this is a proxy to expose connect as a global
// it is mandatory cause react-redux does not have default export, and expose-loader does not work properly with that
//import * as ReduxForm from "redux-form";

export * from "redux-form";
export { default } from "redux-form";