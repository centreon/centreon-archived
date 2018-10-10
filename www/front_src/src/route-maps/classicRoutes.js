import routeMap from "./index";
import Home from "../route-components/home";
import Module from "../route-components/module";
import PollerStepOne from "../route-components/pollerStep1";
import PollerStepTwo from "../route-components/pollerStep2";
import PollerStepThree from "../route-components/pollerStep3";
import RemoteServerStepOne from "../route-components/remoteServerStep1";
import RemoteServerStepTwo from "../route-components/remoteServerStep2";
import RemoteServerStepThree from "../route-components/remoteServerStep3";
import ServerConfigurationWizard from "../route-components/serverConfigurationWizard";
import NotAllowedPage from "../route-components/notAllowedPage";

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
  },
  {
    path: routeMap.pollerStep1,
    comp: PollerStepOne,
    exact: true
  },
  {
    path: routeMap.pollerStep2,
    comp: PollerStepTwo,
    exact: true
  },
  {
    path: routeMap.pollerStep3,
    comp: PollerStepThree,
    exact: true
  },
  {
    path: routeMap.remoteServerStep1,
    comp: RemoteServerStepOne,
    exact: true
  },
  {
    path: routeMap.remoteServerStep2,
    comp: RemoteServerStepTwo,
    exact: true
  },
  {
    path: routeMap.remoteServerStep3,
    comp: RemoteServerStepThree,
    exact: true
  },
  {
    path: routeMap.serverConfigurationWizard,
    comp: ServerConfigurationWizard,
    exact: true
  },
  {
    path: routeMap.notAllowedPage,
    comp: NotAllowedPage,
    exact: true
  }
];

export default classicRoutes;
