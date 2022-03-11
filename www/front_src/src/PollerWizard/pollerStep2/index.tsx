import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { useUpdateAtom, useAtomValue } from 'jotai/utils';
import { pick } from 'ramda';

import {
  Typography,
  Button,
  FormControlLabel,
  Checkbox,
  CircularProgress,
} from '@mui/material';

import {
  postData,
  useRequest,
  MultiAutocompleteField,
  SelectField,
  SelectEntry,
} from '@centreon/ui';

import { pollerAtom, setWizardDerivedAtom, PollerData } from '../PollerAtoms';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';
import routeMap from '../../reactRoutes/routeMap';
import {
  labelAdvancedServerConfiguration,
  labelLinkedRemoteMaster,
  labelLinkedadditionalRemote,
  labelOpenBrokerFlow,
  labelPrevious,
  labelApply,
} from '../translatedLabels';
import { Props, PollerOrRemoteList } from '../models';

const getPollersEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getRemotesList';
const wizardFormEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';

interface stepTwoFormData {
  linked_remote_master: string;
  linked_remote_slaves: Array<SelectEntry>;
  open_broker_flow: boolean;
}
const FormPollerStepTwo = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const navigate = useNavigate();

  const [pollers, setPollers] = React.useState<Array<PollerOrRemoteList>>([]);
  const [stepTwoFormData, setStepTwoFormData] = React.useState<stepTwoFormData>(
    {
      linked_remote_master: '',
      linked_remote_slaves: [],
      open_broker_flow: false,
    },
  );

  const { sendRequest: getPollersRequest } = useRequest<
    Array<PollerOrRemoteList>
  >({
    request: postData,
  });

  const { sendRequest: postWizardFormRequest, sending: loading } = useRequest<{
    success: boolean;
  }>({
    request: postData,
  });

  const pollerData = useAtomValue<PollerData>(pollerAtom);
  const setWizard = useUpdateAtom(setWizardDerivedAtom);

  const getPollers = (): void => {
    getPollersRequest({ data: null, endpoint: getPollersEndpoint }).then(
      setPollers,
    );
  };

  const handleChange = (evt): void => {
    const { value, name } = evt.target;

    if (name === 'open_broker_flow') {
      setStepTwoFormData({
        ...stepTwoFormData,
        open_broker_flow: !stepTwoFormData.open_broker_flow,
      });

      return;
    }
    setStepTwoFormData({
      ...stepTwoFormData,
      [name]: value,
    });
  };

  const changeValue = (_, slaves): void => {
    setStepTwoFormData({
      ...stepTwoFormData,
      linked_remote_slaves: slaves,
    });
  };

  const handleSubmit = (event): void => {
    event.preventDefault();
    const data = {
      ...stepTwoFormData,
      linked_remote_slaves: stepTwoFormData.linked_remote_slaves.map(
        ({ id }) => id,
      ),
    };
    const dataToPost = { ...data, ...pollerData };
    dataToPost.server_type = 'poller';

    postWizardFormRequest({
      data: dataToPost,
      endpoint: wizardFormEndpoint,
    })
      .then(({ success }) => {
        setWizard({ submitStatus: success });
        if (pollerData.linked_remote_master) {
          goToNextStep();
        } else {
          navigate(routeMap.pollerList);
        }
      })
      .catch(() => undefined);
  };

  const linkedRemoteMasterOption = pollers.map((c) => pick(['id', 'name'], c));

  const linkedRemoteSlavesOption = pollers
    .filter((poller) => poller.id !== stepTwoFormData.linked_remote_master)
    .map((c) => pick(['id', 'name'], c));

  React.useEffect(() => {
    getPollers();
  }, []);

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">
          {t(labelAdvancedServerConfiguration)}
        </Typography>
      </div>
      <form onSubmit={handleSubmit}>
        <div className={classes.form}>
          {linkedRemoteMasterOption.length !== 0 && (
            <SelectField
              fullWidth
              label={t(labelLinkedRemoteMaster)}
              name="linked_remote_master"
              options={linkedRemoteMasterOption}
              selectedOptionId={stepTwoFormData.linked_remote_master}
              onChange={handleChange}
            />
          )}
          {stepTwoFormData.linked_remote_master &&
            linkedRemoteSlavesOption.length >= 2 && (
              <MultiAutocompleteField
                fullWidth
                label={t(labelLinkedadditionalRemote)}
                options={linkedRemoteSlavesOption}
                value={stepTwoFormData.linked_remote_slaves}
                onChange={changeValue}
              />
            )}
          <FormControlLabel
            control={
              <Checkbox
                checked={stepTwoFormData.open_broker_flow}
                name="open_broker_flow"
                onChange={handleChange}
              />
            }
            label={`${t(labelOpenBrokerFlow)}`}
          />
          <div className={classes.formButton}>
            <Button size="small" type="button" onClick={goToPreviousStep}>
              {t(labelPrevious)}
            </Button>
            <Button
              color="primary"
              disabled={loading}
              endIcon={loading && <CircularProgress size={15} />}
              size="small"
              type="submit"
              variant="contained"
            >
              {t(labelApply)}
            </Button>
          </div>
        </div>
      </form>
    </div>
  );
};

export default FormPollerStepTwo;
