import * as React from 'react';

import { useNavigate } from 'react-router-dom';
import { equals } from 'ramda';

import Form from '../../components/forms/ServerConfigurationWizardForm';
import routeMap from '../../route-maps/route-map';
import ProgressBar from '../../components/progressBar';
import BaseWizard from '../../components/forms/baseWizard';

const links = [
  { active: true, number: 1, path: routeMap.serverConfigurationWizard },
  { active: false, number: 2 },
  { active: false, number: 3 },
  { active: false, number: 4 },
];

const ServerConfigurationWizardRoute = (): JSX.Element => {
  const navigate = useNavigate();

  const handleSubmit = ({ server_type }): void => {
    if (equals(server_type, '1')) {
      navigate(routeMap.remoteServerStep1);
    }
    if (equals(server_type, '2')) {
      navigate(routeMap.pollerStep1);
    }
  };

  return (
    <BaseWizard>
      <ProgressBar links={links} />
      <Form onSubmit={handleSubmit} />
    </BaseWizard>
  );
};

export default ServerConfigurationWizardRoute;
