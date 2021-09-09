/* eslint-disable no-nested-ternary */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-restricted-globals */

import React from 'react';

import cardStyles from '../Card/card.scss';
import Wrapper from '../Wrapper';
import HorizontalLineContent from '../HorizontalLines/HorizontalLineContent';
import Card from '../Card';
import CardItem from '../Card/CardItem';
import IconInfo from '../Icon/IconInfo';
import Title from '../Title';
import Subtitle from '../Subtitle';
import Button from '../Button';
import IconContent from '../Icon/IconContent';
import ButtonAction from '../Button/ButtonAction';

class ExtensionsHolder extends React.Component {
  // remove "centreon" word from the begin of the module/widget description
  parseDescription = (description) => {
    return description.replace(/^centreon\s+(\w+)/i, (_, $1) => $1);
  };

  // get CardItem props to display license information in footer
  getPropsFromLicense = (licenseInfo) => {
    let licenseProps = {};

    if (licenseInfo && licenseInfo.required) {
      if (!licenseInfo.expiration_date) {
        licenseProps = {
          itemFooterColor: 'red',
          itemFooterLabel: 'License required',
        };
      } else if (!isNaN(Date.parse(licenseInfo.expiration_date))) {
        // @todo move this logic to centreon. Furthermore, it will facilitate translation
        // @todo use moment to convert date in the proper format (locale and timezone from user)
        const expirationDate = new Date(licenseInfo.expiration_date);
        licenseProps = {
          itemFooterColor: 'green',
          itemFooterLabel: `License expires ${expirationDate
            .toISOString()
            .slice(0, 10)}`,
        };
      } else {
        licenseProps = {
          itemFooterColor: 'red',
          itemFooterLabel: 'License not valid',
        };
      }
    }

    return licenseProps;
  };

  render() {
    const {
      title,
      entities,
      onCardClicked,
      onDelete,
      titleColor,
      hrColor,
      hrTitleColor,
      onInstall,
      onUpdate,
      updating,
      installing,
      type,
    } = this.props;

    return (
      <Wrapper>
        <HorizontalLineContent
          hrColor={hrColor}
          hrTitle={title}
          hrTitleColor={hrTitleColor}
        />
        <Card>
          <div>
            {entities.map((entity) => {
              return (
                <div
                  className={cardStyles['card-inline']}
                  id={`${type}-${entity.id}`}
                  key={entity.id}
                  onClick={() => {
                    onCardClicked(entity.id, type);
                  }}
                >
                  <CardItem
                    itemBorderColor={
                      entity.version.installed
                        ? !entity.version.outdated
                          ? 'green'
                          : 'orange'
                        : 'gray'
                    }
                    {...this.getPropsFromLicense(entity.license)}
                  >
                    {entity.version.installed ? (
                      <IconInfo
                        iconColor="green"
                        iconName="state"
                        iconPosition="info-icon-position"
                      />
                    ) : null}

                    <Title
                      label={this.parseDescription(entity.description)}
                      labelStyle={{ fontSize: '16px' }}
                      title={entity.description}
                      titleColor={titleColor}
                    >
                      <Subtitle label={`by ${entity.label}`} />
                    </Title>
                    <Button
                      buttonType={
                        entity.version.installed
                          ? entity.version.outdated
                            ? 'regular'
                            : 'bordered'
                          : 'regular'
                      }
                      color={
                        entity.version.installed
                          ? entity.version.outdated
                            ? 'orange'
                            : 'blue'
                          : 'green'
                      }
                      customClass="button-card-position"
                      label={
                        (!entity.version.installed ? 'Available ' : '') +
                        entity.version.available
                      }
                      style={{
                        cursor: entity.version.installed
                          ? 'default'
                          : 'pointer',
                        opacity:
                          installing[entity.id] || updating[entity.id]
                            ? '0.5'
                            : 'inherit',
                      }}
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
                      {!entity.version.installed ? (
                        <IconContent
                          customClass="content-icon-button"
                          iconContentColor="white"
                          iconContentType={`${
                            installing[entity.id] ? 'update' : 'add'
                          }`}
                          loading={installing[entity.id]}
                        />
                      ) : entity.version.outdated ? (
                        <IconContent
                          customClass="content-icon-button"
                          iconContentColor="white"
                          iconContentType="update"
                          loading={updating[entity.id]}
                        />
                      ) : null}
                    </Button>
                    {entity.version.installed ? (
                      <ButtonAction
                        buttonActionType="delete"
                        buttonIconType="delete"
                        customPosition="button-action-card-position"
                        iconColor="gray"
                        onClick={(e) => {
                          e.preventDefault();
                          e.stopPropagation();

                          onDelete(entity, type);
                        }}
                      />
                    ) : null}
                  </CardItem>
                </div>
              );
            })}
          </div>
        </Card>
      </Wrapper>
    );
  }
}

export default ExtensionsHolder;
