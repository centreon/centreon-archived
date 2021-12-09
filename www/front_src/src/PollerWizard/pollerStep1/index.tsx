import * as React from 'react';

import { connect } from 'react-redux';

import { postData, useRequest } from '@centreon/ui';

import Form from '../forms/poller/PollerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import BaseWizard from '../forms/baseWizard';

const links = [
  {
    active: true,
    number: 1,
    path: routeMap.serverConfigurationWizard,
    prevActive: true,
  },
  { active: true, number: 2, path: routeMap.pollerStep1 },
  { active: false, number: 3 },
  { active: false, number: 4 },
];

const pollerWaitListEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getPollerWaitList';

interface Props {
  goToNextStep: () => void;
  setWizard: (pollerWizard) => Record<string, unknown>;
}

const FormPollerStepOne = ({ setWizard, goToNextStep }: Props): JSX.Element => {
  const [waitList, setWaitList] = React.useState<Array<unknown> | null>(null);
  const { sendRequest } = useRequest<Array<unknown>>({
    request: postData,
  });

  const getWaitList = (): void => {
    sendRequest({
      data: null,
      endpoint: pollerWaitListEndpoint,
    })
      .then((data): void => {
        setWaitList(data);
      })
      .catch(() => {
        setWaitList([]);
      });
  };

  React.useEffect(() => {
    getWaitList();
  }, []);

  const handleSubmit = (data): void => {
    setWizard(data);
    goToNextStep();
  };

  return (
    <BaseWizard>
      <ProgressBar links={links} />
      <Form initialValues={{}} waitList={waitList} onSubmit={handleSubmit} />
    </BaseWizard>
  );
};

const mapDispatchToProps = {
  setWizard: setPollerWizard,
};

const PollerStepOne = connect(null, mapDispatchToProps)(FormPollerStepOne);

export default (props: Pick<Props, 'goToNextStep'>): JSX.Element => {
  return <PollerStepOne {...props} />;
};
