import * as React from 'react';

import { connect } from 'react-redux';

import { postData, useRequest } from '@centreon/ui';

import Form from '../forms/remoteServer/RemoteServerFormStepOne';
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
  { active: true, number: 2, path: routeMap.remoteServerStep1 },
  { active: false, number: 3 },
  { active: false, number: 4 },
];

const remoteServerWaitListEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getWaitList';

interface Props {
  goToNextStep: () => void;
  pollerData: Record<string, unknown>;
  setWizard: (pollerWizard) => Record<string, unknown>;
}

const FormRemoteServerStepOne = ({
  setWizard,
  pollerData,
  goToNextStep,
}: Props): JSX.Element => {
  const [waitList, setWaitList] = React.useState<Array<unknown> | null>(null);
  const { sendRequest } = useRequest<Array<unknown>>({
    request: postData,
  });

  const getWaitList = (): void => {
    sendRequest({
      data: null,
      endpoint: remoteServerWaitListEndpoint,
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
      <Form
        initialValues={{ ...pollerData, centreon_folder: '/centreon/' }}
        waitList={waitList}
        onSubmit={handleSubmit}
      />
    </BaseWizard>
  );
};

const mapStateToProps = ({ pollerForm }): Pick<Props, 'pollerData'> => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setWizard: setPollerWizard,
};

const RemoteServerStepOne = connect(
  mapStateToProps,
  mapDispatchToProps,
)(FormRemoteServerStepOne);

export default (props: Pick<Props, 'goToNextStep'>): JSX.Element => {
  return <RemoteServerStepOne {...props} />;
};
