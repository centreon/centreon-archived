/* eslint-disable no-nested-ternary */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-restricted-globals */

import React from 'react';

import DeleteIcon from '@material-ui/icons/Delete';
import UpdateIcon from '@material-ui/icons/SystemUpdateAlt';
import CheckIcon from '@material-ui/icons/Check';
import InstallIcon from '@material-ui/icons/Add';
import {
  Card,
  CardActions,
  CardContent,
  Paper,
  Button,
  Typography,
  Grid,
  Chip,
  LinearProgress,
} from '@material-ui/core';

import Wrapper from '../Wrapper';

class ExtensionsHolder extends React.Component {
  parseDescription = (description) => {
    return description.replace(/^centreon\s+(\w+)/i, (_, $1) => $1);
  };

  getPropsFromLicense = (licenseInfo) => {
    if (licenseInfo && licenseInfo.required) {
      if (!licenseInfo.expiration_date) {
        return {
          color: '#f90026',
          label: 'License required',
        };
      }
      if (!isNaN(Date.parse(licenseInfo.expiration_date))) {
        // @todo use moment to convert date in the proper format (locale and timezone from user)
        const expirationDate = new Date(licenseInfo.expiration_date);

        return {
          color: '#84BD00',
          label: `License expires ${expirationDate.toISOString().slice(0, 10)}`,
        };
      }

      return {
        color: '#f90026',
        label: 'License not valid',
      };
    }

    return undefined;
  };

  render() {
    const {
      title,
      entities,
      onCardClicked,
      onDelete,
      onInstall,
      onUpdate,
      updating,
      installing,
      deletingEntityId,
      type,
    } = this.props;

    return (
      <Wrapper>
        <Typography style={{ marginBottom: 8 }} variant="body1">
          {title}
        </Typography>
        <Grid
          container
          alignItems="stretch"
          spacing={2}
          style={{ cursor: 'pointer' }}
        >
          {entities.map((entity) => {
            const isLoading =
              installing[entity.id] ||
              updating[entity.id] ||
              deletingEntityId === entity.id;

            const licenseInfo = this.getPropsFromLicense(entity.license);

            return (
              <Grid
                item
                id={`${type}-${entity.id}`}
                key={entity.id}
                style={{ width: 200 }}
                onClick={() => {
                  onCardClicked(entity.id, type);
                }}
              >
                <Card
                  style={{ display: 'grid', height: '100%' }}
                  variant="outlined"
                >
                  <div style={{ height: 10 }}>
                    {isLoading && <LinearProgress />}
                  </div>
                  <CardContent>
                    {/* {entity.version.installed ? <InfoIcon /> : null} */}

                    <Typography style={{ fontWeight: 'bold' }} variant="body1">
                      {this.parseDescription(entity.description)}
                    </Typography>
                    <Typography variant="body2">
                      {`by ${entity.label}`}
                    </Typography>
                  </CardContent>
                  <CardActions>
                    {entity.version.installed ? (
                      <Chip
                        avatar={
                          entity.version.outdated ? (
                            <UpdateIcon
                              style={{
                                color: '#FFFFFF',
                                cursor: 'pointer',
                              }}
                              onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();

                                onUpdate(entity.id, type);
                              }}
                            />
                          ) : (
                            <CheckIcon style={{ color: '#FFFFFF' }} />
                          )
                        }
                        deleteIcon={<DeleteIcon style={{ color: '#FFFFFF' }} />}
                        disabled={isLoading}
                        label={entity.version.current}
                        style={{
                          backgroundColor: entity.version.outdated
                            ? '#FF9A13'
                            : '#84BD00',
                          color: '#FFFFFF',
                        }}
                        onDelete={() => onDelete(entity, type)}
                      />
                    ) : (
                      <Button
                        color="primary"
                        disabled={isLoading}
                        size="small"
                        startIcon={!entity.version.installed && <InstallIcon />}
                        variant="contained"
                        onClick={(e) => {
                          e.preventDefault();
                          e.stopPropagation();
                          const { id } = entity;
                          const { version } = entity;
                          if (version.outdated && !updating[entity.id]) {
                            onUpdate(id, type);
                          } else if (
                            !version.installed &&
                            !installing[entity.id]
                          ) {
                            onInstall(id, type);
                          }
                        }}
                      >
                        {entity.version.available}
                      </Button>
                    )}
                  </CardActions>

                  {licenseInfo && (
                    <Paper
                      square
                      style={{
                        alignItems: 'center',
                        backgroundColor: licenseInfo.color,
                        cursor: 'pointer',
                        display: 'flex',
                        justifyContent: 'center',
                      }}
                      variant="outlined"
                    >
                      <Typography style={{ color: '#FFFFFF' }} variant="body2">
                        {licenseInfo.label}
                      </Typography>
                    </Paper>
                  )}
                </Card>
              </Grid>
            );
          })}
        </Grid>
        {/* </Paper> */}
      </Wrapper>
    );
  }
}

export default ExtensionsHolder;
