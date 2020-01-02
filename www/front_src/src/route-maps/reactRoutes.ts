import routeMap from './route-map.ts';
import PollerStepOne from '../route-components/pollerStep1/index.tsx';
import PollerStepTwo from '../route-components/pollerStep2/index.tsx';
import PollerStepThree from '../route-components/pollerStep3/index.tsx';
import RemoteServerStepOne from '../route-components/remoteServerStep1/index.tsx';
import RemoteServerStepTwo from '../route-components/remoteServerStep2/index.tsx';
import RemoteServerStepThree from '../route-components/remoteServerStep3/index.tsx';
import ServerConfigurationWizard from '../route-components/serverConfigurationWizard/index.tsx';
import ExtensionsManagerPage from '../route-components/administration/extensions/manager/index.tsx';
import NotAllowedPage from '../route-components/notAllowedPage/index.tsx';

interface ReactRoute {
  path: string;
  comp: any; // to be remplaced by ReactNode when types definition will be included
  exact: boolean;
}

const reactRoutes: Array<ReactRoute> = [
  {
    path: routeMap.pollerStep1,
    comp: PollerStepOne,
    exact: true,
  },
  {
    path: routeMap.pollerStep2,
    comp: PollerStepTwo,
    exact: true,
  },
  {
    path: routeMap.pollerStep3,
    comp: PollerStepThree,
    exact: true,
  },
  {
    path: routeMap.remoteServerStep1,
    comp: RemoteServerStepOne,
    exact: true,
  },
  {
    path: routeMap.remoteServerStep2,
    comp: RemoteServerStepTwo,
    exact: true,
  },
  {
    path: routeMap.remoteServerStep3,
    comp: RemoteServerStepThree,
    exact: true,
  },
  {
    path: routeMap.serverConfigurationWizard,
    comp: ServerConfigurationWizard,
    exact: true,
  },
  {
    path: routeMap.extensionsManagerPage,
    comp: ExtensionsManagerPage,
    exact: true,
  },
  {
    path: routeMap.notAllowedPage,
    comp: NotAllowedPage,
    exact: true,
  },
];

export default reactRoutes;
