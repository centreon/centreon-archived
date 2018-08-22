import routeMap from "./index";
import Home from "../route-components/home";
import Module from "../route-components/module";

const classicRoutes = [
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
