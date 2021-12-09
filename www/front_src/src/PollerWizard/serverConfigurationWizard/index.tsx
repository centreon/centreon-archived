import * as React from 'react';

import { equals } from 'ramda';

import routeMap from '../../route-maps/route-map';
import ProgressBar from '../../components/progressBar';
import BaseWizard from '../forms/baseWizard';
import Form from '../forms/ServerConfigurationWizardForm';

const links = [
  { active: true, number: 1, path: routeMap.serverConfigurationWizard },
  { active: false, number: 2 },
  { active: false, number: 3 },
  { active: false, number: 4 },
];

interface Props {
  changeServerType: (type: number) => void;
  goToNextStep: () => void;
}

const ServerConfigurationWizard = ({
  changeServerType,
  goToNextStep,
}: Props): JSX.Element => {
  const handleSubmit = ({ server_type }): void => {
    if (equals(server_type, '1')) {
      changeServerType(0);
    }
    if (equals(server_type, '2')) {
      changeServerType(1);
    }

    goToNextStep();
  };

  return (
    <BaseWizard>
      <ProgressBar links={links} />
      <Form onSubmit={handleSubmit} />
    </BaseWizard>
  );
};

export default ServerConfigurationWizard;
