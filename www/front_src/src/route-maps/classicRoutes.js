import routeMap from "./route-map";
import Home from "../route-components/home";
import Module from "../route-components/module";
import Extensions from '../route-components/extensions';

const classicRoutes = [
  {
    path:routeMap.extensions,
    comp:Extensions,
    exact:true
  },
  {
    path: routeMap.home,
    comp: Home,
    exact: true
  },
  {
    path: routeMap.module,
    comp: Module,
    exact: true
  }
];

export default classicRoutes;
