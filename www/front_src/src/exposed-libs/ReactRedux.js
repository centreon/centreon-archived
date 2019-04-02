// this is a proxy to expose connect as a global
// it is mandatory cause react-redux does not have default export, and expose-loader does not work properly with that
import { connect as reduxConnect } from "react-redux";

export const connect = reduxConnect;
export default reduxConnect;