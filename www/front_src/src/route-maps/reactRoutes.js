import routeMap from './route-map';
import PollerStepOne from '../route-components/pollerStep1';
import PollerStepTwo from '../route-components/pollerStep2';
import PollerStepThree from '../route-components/pollerStep3';
import RemoteServerStepOne from '../route-components/remoteServerStep1';
import RemoteServerStepTwo from '../route-components/remoteServerStep2';
import RemoteServerStepThree from '../route-components/remoteServerStep3';
import ServerConfigurationWizard from '../route-components/serverConfigurationWizard';
import ExtensionsManagerPage from '../route-components/administration/extensions/manager';
import NotAllowedPage from '../route-components/notAllowedPage';

const reactRoutes = [
  {
    path: routeMap.pollerStep1,
    comp: PollerStepOne,
  },
  {
    path: routeMap.pollerStep2,
    comp: PollerStepTwo,
  },
  {
    path: routeMap.pollerStep3,
    comp: PollerStepThree,
  },
  {
    path: routeMap.remoteServerStep1,
    comp: RemoteServerStepOne,
  },
  {
    path: routeMap.remoteServerStep2,
    comp: RemoteServerStepTwo,
  },
  {
    path: routeMap.remoteServerStep3,
    comp: RemoteServerStepThree,
  },
  {
    path: routeMap.serverConfigurationWizard,
    comp: ServerConfigurationWizard,
  },
  {
    path: routeMap.extensionsManagerPage,
    comp: ExtensionsManagerPage,
  },
  {
    path: routeMap.notAllowedPage,
    comp: NotAllowedPage,
  },
];

export default reactRoutes;
