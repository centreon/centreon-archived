// this is a proxy to expose Link as a global
// it is mandatory cause react-router-dom does not have default export, and expose-loader does not work properly with that
import { Link as RouterLink, Route as RouterRouter, withRouter as RouterwithRouter } from "react-router-dom";

export const Link = RouterLink;
export const Route = RouterRouter;
export const withRouter = RouterwithRouter;
export default RouterLink;